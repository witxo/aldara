import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import '../i18n/app_localizations.dart';

class StatusBadge extends StatelessWidget {
  final String? status;
  final double fontSize;

  const StatusBadge(this.status, {super.key, this.fontSize = 11});

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    final color = AppColors.statusColor(status);
    final bgColor = AppColors.statusBgColor(status);
    final label = _statusLabel(l, status);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: fontSize,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  String _statusLabel(AppLocalizations l, String? status) {
    switch (status) {
      case 'confirmed': return l.translate('reservation.status_confirmed');
      case 'pending': return l.translate('reservation.status_pending');
      case 'completed': return l.translate('reservation.status_completed');
      case 'cancelled': return l.translate('reservation.status_cancelled');
      case 'checkin_sent': return l.translate('reservation.status_checkin_sent');
      case 'partial': return l.translate('reservation.status_partial');
      case 'active': return l.translate('common.yes');
      case 'inactive': return l.translate('common.no');
      case 'verified': return l.translate('checkin.status_verified');
      case 'rejected': return l.translate('checkin.status_rejected');
      case 'in_progress': return l.translate('common.loading');
      default: return status ?? '-';
    }
  }
}
