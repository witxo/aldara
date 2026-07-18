import 'package:flutter/material.dart';

class ScannerOverlay extends StatelessWidget {
  final Color borderColor;
  final String label;

  const ScannerOverlay({
    super.key,
    this.borderColor = Colors.white,
    this.label = 'Coloca el documento dentro del recuadro',
  });

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final width = constraints.maxWidth;
        final height = constraints.maxHeight;
        final scanWidth = width * 0.8;
        final scanHeight = scanWidth * 0.63;
        final left = (width - scanWidth) / 2;
        final top = (height - scanHeight) / 2 - 40;

        return Stack(
          children: [
            // Dark overlay with transparent cutout
            ClipPath(
              clipper: _ScannerOverlayClipper(
                rect: Rect.fromLTWH(left, top, scanWidth, scanHeight),
              ),
              child: Container(
                color: Colors.black.withOpacity(0.5),
                width: width,
                height: height,
              ),
            ),
            // Corner brackets
            CustomPaint(
              size: Size(width, height),
              painter: _CornerPainter(
                rect: Rect.fromLTWH(left, top, scanWidth, scanHeight),
                color: borderColor,
              ),
            ),
            // Label
            Positioned(
              left: 0,
              right: 0,
              top: top + scanHeight + 24,
              child: Text(
                label,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
          ],
        );
      },
    );
  }
}

class _ScannerOverlayClipper extends CustomClipper<Path> {
  final Rect rect;

  _ScannerOverlayClipper({required this.rect});

  @override
  Path getClip(Size size) {
    final path = Path()
      ..addRect(Rect.fromLTWH(0, 0, size.width, size.height))
      ..addRect(rect)
      ..fillType = PathFillType.evenOdd;
    return path;
  }

  @override
  bool shouldReclip(_ScannerOverlayClipper oldClipper) =>
      rect != oldClipper.rect;
}

class _CornerPainter extends CustomPainter {
  final Rect rect;
  final Color color;

  _CornerPainter({required this.rect, required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..strokeWidth = 3
      ..style = PaintingStyle.stroke;

    const cornerLength = 30.0;

    // Top-left
    canvas.drawLine(
      rect.topLeft + Offset(0, cornerLength),
      rect.topLeft,
      paint,
    );
    canvas.drawLine(
      rect.topLeft,
      rect.topLeft + Offset(cornerLength, 0),
      paint,
    );

    // Top-right
    canvas.drawLine(
      rect.topRight + Offset(-cornerLength, 0),
      rect.topRight,
      paint,
    );
    canvas.drawLine(
      rect.topRight,
      rect.topRight + Offset(0, cornerLength),
      paint,
    );

    // Bottom-left
    canvas.drawLine(
      rect.bottomLeft + Offset(0, -cornerLength),
      rect.bottomLeft,
      paint,
    );
    canvas.drawLine(
      rect.bottomLeft,
      rect.bottomLeft + Offset(cornerLength, 0),
      paint,
    );

    // Bottom-right
    canvas.drawLine(
      rect.bottomRight + Offset(-cornerLength, 0),
      rect.bottomRight,
      paint,
    );
    canvas.drawLine(
      rect.bottomRight,
      rect.bottomRight + Offset(0, -cornerLength),
      paint,
    );

    // Scanning line animation placeholder
    final scanLine = Paint()
      ..color = color.withOpacity(0.3)
      ..strokeWidth = 1;

    final centerY = rect.top + rect.height * 0.45;
    canvas.drawLine(
      Offset(rect.left + 10, centerY),
      Offset(rect.right - 10, centerY),
      scanLine,
    );
  }

  @override
  bool shouldRepaint(_CornerPainter oldDelegate) =>
      rect != oldDelegate.rect || color != oldDelegate.color;
}
