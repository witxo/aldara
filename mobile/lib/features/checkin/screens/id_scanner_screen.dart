import 'dart:io';
import 'dart:math';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import 'package:google_mlkit_text_recognition/google_mlkit_text_recognition.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:image_picker/image_picker.dart';
import '../services/id_parser.dart';
import '../widgets/scanner_overlay.dart';

class IdScannerResult {
  final String documentType;
  final String documentNumber;
  final String? firstName;
  final String? lastName;
  final String? birthDate;
  final String? nationality;
  final String? gender;
  final String? expiryDate;
  final double confidence;

  IdScannerResult({
    required this.documentType,
    required this.documentNumber,
    this.firstName,
    this.lastName,
    this.birthDate,
    this.nationality,
    this.gender,
    this.expiryDate,
    this.confidence = 0.0,
  });
}

class IdScannerScreen extends StatefulWidget {
  const IdScannerScreen({super.key});

  @override
  State<IdScannerScreen> createState() => _IdScannerScreenState();
}

class _IdScannerScreenState extends State<IdScannerScreen>
    with WidgetsBindingObserver {
  CameraController? _cameraController;
  final TextRecognizer _textRecognizer =
      TextRecognizer(script: TextRecognitionScript.latin);
  final ImagePicker _imagePicker = ImagePicker();
  final int _maxCaptures = 10;
  final double _confidenceThreshold = 0.55;

  bool _isProcessing = false;
  bool _cameraReady = false;
  String? _statusMessage;
  int _captureCount = 0;
  double _bestConfidence = 0.0;
  IdScannerResult? _bestResult;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _initCamera();
  }

  Future<void> _initCamera() async {
    final status = await Permission.camera.request();
    if (!status.isGranted) {
      setState(() => _statusMessage = 'Permiso de cámara denegado');
      return;
    }

    final cameras = await availableCameras();
    if (cameras.isEmpty) {
      setState(() => _statusMessage = 'No se detectó cámara');
      return;
    }

    final camera = cameras.firstWhere(
      (c) => c.lensDirection == CameraLensDirection.back,
      orElse: () => cameras.first,
    );

    _cameraController = CameraController(
      camera,
      ResolutionPreset.medium,
      enableAudio: false,
      imageFormatGroup: ImageFormatGroup.nv21,
    );

    try {
      await _cameraController!.initialize();
      if (mounted) {
        setState(() => _cameraReady = true);
        _startContinuousScan();
      }
    } catch (e) {
      setState(() => _statusMessage = 'Error al iniciar cámara: $e');
    }
  }

  void _startContinuousScan() {
    Future.delayed(const Duration(milliseconds: 1500), _captureAndAnalyze);
  }

  Future<void> _captureAndAnalyze() async {
    if (!_cameraReady || _isProcessing || !mounted) return;

    _isProcessing = true;

    try {
      final picture = await _cameraController!.takePicture();
      final result = await _analyzeImage(InputImage.fromFilePath(picture.path));

      if (result != null && mounted) {
        setState(() {
          _captureCount++;
          if (result.confidence > _bestConfidence) {
            _bestConfidence = result.confidence;
            _bestResult = result;
          }
        });

        if (result.confidence >= _confidenceThreshold) {
          setState(() => _statusMessage =
              'Documento detectado: ${result.documentType.toUpperCase()} (${(result.confidence * 100).toStringAsFixed(0)}%)');
          await Future.delayed(const Duration(milliseconds: 500));
          if (mounted) {
            Navigator.pop(context, result);
            return;
          }
        }
      }
    } catch (_) {}

    _isProcessing = false;

    if (mounted) {
      if (_captureCount >= _maxCaptures) {
        if (_bestResult != null) {
          Navigator.pop(context, _bestResult);
        } else {
          setState(() {
            _statusMessage = 'No se pudo leer el documento. Intenta de nuevo.';
          });
        }
      } else {
        _startContinuousScan();
      }
    }
  }

  Future<IdScannerResult?> _analyzeImage(InputImage inputImage) async {
    final recognisedText = await _textRecognizer.processImage(inputImage);
    final text = recognisedText.text;

    if (text.isEmpty) return null;

    final parsed = IdParser.parse(text);
    if (parsed == null || parsed.documentNumber.isEmpty) return null;

    setState(() => _statusMessage =
        'Documento: ${parsed.documentType.toUpperCase()} (${(parsed.confidence * 100).toStringAsFixed(0)}%)');

    return IdScannerResult(
      documentType: parsed.documentType,
      documentNumber: parsed.documentNumber,
      firstName: parsed.firstName,
      lastName: parsed.lastName,
      birthDate: parsed.birthDate,
      nationality: parsed.nationality,
      gender: parsed.gender,
      expiryDate: parsed.expiryDate,
      confidence: parsed.confidence,
    );
  }

  Future<void> _pickFromGallery() async {
    final file = await _imagePicker.pickImage(source: ImageSource.gallery);
    if (file == null) return;

    setState(() => _isProcessing = true);

    final result = await _analyzeImage(InputImage.fromFilePath(file.path));
    if (result != null && mounted) {
      Navigator.pop(context, result);
    } else {
      setState(() {
        _isProcessing = false;
        _statusMessage = 'No se pudo leer el documento. Intenta de nuevo.';
      });
    }
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (_cameraController == null || !_cameraController!.value.isInitialized) {
      return;
    }
    if (state == AppLifecycleState.resumed) {
      _cameraController?.resumePreview();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _textRecognizer.close();
    _cameraController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Escanear documento'),
        actions: [
          IconButton(
            icon: const Icon(Icons.photo_library_outlined),
            onPressed: _pickFromGallery,
            tooltip: 'Seleccionar de galería',
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (!_cameraReady) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const CircularProgressIndicator(),
            const SizedBox(height: 16),
            Text(_statusMessage ?? 'Iniciando cámara...'),
          ],
        ),
      );
    }

    return Stack(
      children: [
        if (_cameraController != null && _cameraController!.value.isInitialized)
          SizedBox(
            width: double.infinity,
            height: double.infinity,
            child: FittedBox(
              fit: BoxFit.cover,
              child: SizedBox(
                width: _cameraController!.value.previewSize!.width,
                height: _cameraController!.value.previewSize!.height,
                child: CameraPreview(_cameraController!),
              ),
            ),
          ),

        const ScannerOverlay(),

        Positioned(
          left: 16,
          right: 16,
          bottom: MediaQuery.of(context).padding.bottom + 16,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (_statusMessage != null)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(
                    color: Colors.black87,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _statusMessage!,
                    style: const TextStyle(color: Colors.white, fontSize: 14),
                    textAlign: TextAlign.center,
                  ),
                ),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  IconButton.filled(
                    onPressed: _pickFromGallery,
                    icon: const Icon(Icons.photo_library_outlined),
                    style: IconButton.styleFrom(
                      backgroundColor: Colors.white.withOpacity(0.9),
                      foregroundColor: Colors.black87,
                    ),
                  ),
                  const SizedBox(width: 20),
                  ElevatedButton.icon(
                    onPressed: _isProcessing ? null : _captureAndAnalyze,
                    icon: _isProcessing
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Icon(Icons.camera_alt),
                    label: Text(_isProcessing ? 'Procesando...' : 'Capturar'),
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 14),
                      textStyle: const TextStyle(fontSize: 16),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }
}
