import 'package:flutter/material.dart';

class AppColors {
  static const primary = Color(0xFF2563EB);
  static const primaryLight = Color(0xFF60A5FA);
  static const primaryDark = Color(0xFF1D4ED8);
  static const primaryBg = Color(0xFFEFF6FF);
  static const success = Color(0xFF16A34A);
  static const successBg = Color(0xFFDCFCE7);
  static const warning = Color(0xFFD97706);
  static const warningBg = Color(0xFFFEF9C3);
  static const danger = Color(0xFFDC2626);
  static const dangerBg = Color(0xFFFEE2E2);
  static const surface = Color(0xFFF9FAFB);
  static const textPrimary = Color(0xFF111827);
  static const textSecondary = Color(0xFF6B7280);
  static const textMuted = Color(0xFF9CA3AF);
  static const border = Color(0xFFE5E7EB);
  static const inputBorder = Color(0xFFD1D5DB);
  static const white = Color(0xFFFFFFFF);
  static const black = Color(0xFF000000);

  static const statusConfirmed = Color(0xFF2563EB);
  static const statusPending = Color(0xFFD97706);
  static const statusCompleted = Color(0xFF16A34A);
  static const statusCancelled = Color(0xFFDC2626);
  static const statusDefault = Color(0xFF6B7280);

  static Color statusColor(String? status) {
    switch (status) {
      case 'confirmed':
      case 'active':
      case 'verified':
        return statusConfirmed;
      case 'pending':
      case 'checkin_sent':
      case 'in_progress':
        return statusPending;
      case 'completed':
        return statusCompleted;
      case 'cancelled':
      case 'rejected':
      case 'inactive':
        return statusCancelled;
      default:
        return statusDefault;
    }
  }

  static Color statusBgColor(String? status) {
    switch (status) {
      case 'confirmed':
      case 'active':
      case 'verified':
        return const Color(0xFFDBEAFE);
      case 'pending':
      case 'checkin_sent':
      case 'in_progress':
        return const Color(0xFFFEF3C7);
      case 'completed':
        return const Color(0xFFDCFCE7);
      case 'cancelled':
      case 'rejected':
      case 'inactive':
        return const Color(0xFFFEE2E2);
      default:
        return const Color(0xFFF3F4F6);
    }
  }
}
