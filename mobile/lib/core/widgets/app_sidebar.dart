import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../theme/app_colors.dart';
import '../i18n/app_localizations.dart';
import '../../features/auth/providers/auth_provider.dart';

class AppSidebar extends StatelessWidget {
  final String currentRoute;
  final bool isSuperadmin;

  const AppSidebar({super.key, required this.currentRoute, this.isSuperadmin = false});

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    final auth = context.watch<AuthProvider>();
    final tenantName = auth.selectedTenant?['company_name'] as String? ?? l.translate('app.name');

    return Drawer(
      child: Column(
        children: [
          DrawerHeader(
            decoration: const BoxDecoration(color: AppColors.white),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 40, height: 40,
                      decoration: BoxDecoration(
                        color: AppColors.primary,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Center(child: Icon(Icons.check_circle_outline, color: Colors.white, size: 24)),
                    ),
                    const SizedBox(width: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(l.translate('app.name'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.primary)),
                        Text(tenantName, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 8),
              children: [
                _navItem(context, Icons.home_outlined, l.translate('nav.dashboard'), '/home', 'dashboard'),
                _navItem(context, Icons.calendar_today_outlined, l.translate('nav.reservations'), '/reservations', 'reservations'),
                _navItem(context, Icons.business_outlined, l.translate('nav.properties'), '/properties', 'properties'),
                _navItem(context, Icons.people_outlined, l.translate('nav.guests'), '/guests', 'guests'),
                _navItem(context, Icons.check_circle_outline, l.translate('nav.checkins'), '/checkins', 'checkins'),
                const Divider(),
                _navItem(context, Icons.settings_outlined, l.translate('nav.integrations'), '/integrations', 'integrations'),
                _navItem(context, Icons.description_outlined, l.translate('nav.ses'), '/ses', 'ses'),
                const Divider(),
                _navItem(context, Icons.credit_card_outlined, l.translate('nav.billing'), '/billing', 'billing'),
                _navItem(context, Icons.group_outlined, l.translate('nav.users'), '/users', 'users'),
                _navItem(context, Icons.tune_outlined, l.translate('nav.settings'), '/settings', 'settings'),
                if (isSuperadmin) ...[
                  const Divider(),
                  _navItem(context, Icons.admin_panel_settings_outlined, l.translate('nav.admin'), '/admin', 'admin'),
                ],
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: const BoxDecoration(
              border: Border(top: BorderSide(color: AppColors.border)),
            ),
            child: SafeArea(
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 16,
                    backgroundColor: AppColors.primaryBg,
                    child: Text(
                      (auth.user?['name'] as String? ?? '?')[0].toUpperCase(),
                      style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(auth.user?['name'] as String? ?? '', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
                        Text(auth.user?['email'] as String? ?? '', style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.logout, size: 20, color: AppColors.textSecondary),
                    onPressed: () async {
                      final confirmed = await showDialog<bool>(
                        context: context,
                        builder: (ctx) => AlertDialog(
                          title: const Text('Cerrar sesión'),
                          content: const Text('¿Estás seguro de que quieres cerrar sesión?'),
                          actions: [
                            TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
                            TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Cerrar sesión')),
                          ],
                        ),
                      );
                      if (confirmed == true) {
                        await auth.logout();
                        if (context.mounted) {
                          Navigator.of(context).pushNamedAndRemoveUntil('/login', (_) => false);
                        }
                      }
                    },
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _navItem(BuildContext context, IconData icon, String label, String route, String pattern) {
    final isActive = currentRoute.startsWith(pattern);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 1),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(8),
          onTap: () {
            Navigator.pushReplacementNamed(context, route);
          },
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: isActive ? AppColors.primaryBg : null,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              children: [
                Icon(icon, size: 20, color: isActive ? AppColors.primary : AppColors.textSecondary),
                const SizedBox(width: 12),
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: isActive ? FontWeight.w600 : FontWeight.w400,
                    color: isActive ? AppColors.primary : AppColors.textPrimary,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
