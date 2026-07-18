import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:signature/signature.dart';

class SignaturePad extends StatefulWidget {
  final ValueChanged<String?>? onSign;
  final double height;

  const SignaturePad({super.key, this.onSign, this.height = 150});

  @override
  State<SignaturePad> createState() => _SignaturePadState();
}

class _SignaturePadState extends State<SignaturePad> {
  final SignatureController _controller = SignatureController(
    penStrokeWidth: 2,
    penColor: Colors.black,
    exportPenColor: Colors.black,
  );

  bool _hasSignature = false;

  @override
  void initState() {
    super.initState();
    _controller.addListener(_onDrawChanged);
  }

  void _onDrawChanged() {
    final hasSign = _controller.points.isNotEmpty;
    if (hasSign != _hasSignature) {
      setState(() => _hasSignature = hasSign);
    }
  }

  Future<void> _exportSignature() async {
    if (!_hasSignature) return;

    final Uint8List? data = await _controller.toPngBytes(
      width: 400,
      height: 150,
    );

    if (data != null) {
      widget.onSign?.call(base64Encode(data));
    }
  }

  Future<void> _clear() async {
    _controller.clear();
    widget.onSign?.call(null);
  }

  @override
  void dispose() {
    _controller.removeListener(_onDrawChanged);
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          height: widget.height,
          decoration: BoxDecoration(
            border: Border.all(color: Colors.grey[300]!),
            borderRadius: BorderRadius.circular(8),
            color: Colors.white,
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(7),
            child: Signature(
              controller: _controller,
              height: widget.height,
              backgroundColor: Colors.white,
            ),
          ),
        ),
        const SizedBox(height: 8),
        Row(
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            if (_hasSignature)
              TextButton.icon(
                onPressed: _clear,
                icon: const Icon(Icons.clear, size: 18),
                label: const Text('Borrar'),
                style: TextButton.styleFrom(foregroundColor: Colors.red),
              ),
            const SizedBox(width: 8),
            ElevatedButton.icon(
              onPressed: _hasSignature ? _exportSignature : null,
              icon: const Icon(Icons.check, size: 18),
              label: const Text('Confirmar firma'),
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              ),
            ),
          ],
        ),
      ],
    );
  }
}
