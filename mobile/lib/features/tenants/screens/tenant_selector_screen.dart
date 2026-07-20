import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../auth/providers/auth_provider.dart';

class TenantSelectorScreen extends StatelessWidget {
  const TenantSelectorScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    final auth = context.watch<AuthProvider>();
    final tenants = auth.tenants;

    return Scaffold(
      backgroundColor: AppColors.surface,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 64, height: 64,
                  decoration: BoxDecoration(
                    color: AppColors.primary,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Center(child: Icon(Icons.check_circle_outline, color: Colors.white, size: 36)),
                ),
                const SizedBox(height: 16),
                Text(l.translate('app.name'), style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: AppColors.primary)),
                const SizedBox(height: 24),
                Text('Seleccione una empresa', style: const TextStyle(fontSize: 16, color: AppColors.textPrimary)),
                const SizedBox(height: 4),
                Text('Elija la empresa a la que desea acceder', style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                const SizedBox(height: 24),
                ...tenants.map((t) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: _TenantCard(
                    companyName: t['company_name'] as String? ?? '',
                    role: t['role'] as String? ?? '',
                    status: t['status'] as String? ?? '',
                    onTap: () => auth.selectTenant(t['id'] as int),
                  ),
                )),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _TenantCard extends StatelessWidget {
  final String companyName;
  final String role;
  final String status;
  final VoidCallback onTap;

  const _TenantCard({required this.companyName, required this.role, required this.status, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.zero,
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 48, height: 48,
                decoration: BoxDecoration(
                  color: AppColors.primaryBg,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Center(
                  child: Text(companyName.isNotEmpty ? companyName[0].toUpperCase() : '?',
                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.primary)),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(companyName, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: role == 'admin' ? AppColors.primaryBg : AppColors.warningBg,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(role == 'admin' ? 'Admin' : 'Operador',
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600,
                              color: role == 'admin' ? AppColors.primary : AppColors.warning)),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          width: 8, height: 8,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: status == 'active' ? AppColors.success : AppColors.textMuted,
                          ),
                        ),
                        const SizedBox(width: 4),
                        Text(status == 'active' ? 'Activo' : 'Inactivo',
                          style: TextStyle(fontSize: 11, color: status == 'active' ? AppColors.success : AppColors.textMuted)),
                      ],
                    ),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right, color: AppColors.textMuted),
            ],
          ),
        ),
      ),
    );
  }
}
