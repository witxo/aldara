import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/empty_state.dart';
import '../../auth/providers/auth_provider.dart';

class UserListScreen extends StatefulWidget {
  const UserListScreen({super.key});

  @override
  State<UserListScreen> createState() => _UserListScreenState();
}

class _UserListScreenState extends State<UserListScreen> {
  List<dynamic> _users = [];
  bool _loading = true;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final api = context.read<AuthProvider>().api;
      if (context.read<AuthProvider>().selectedTenant?['id'] != null) {
        final res = await api.get('/tenants/${context.read<AuthProvider>().selectedTenant!['id']}/users');
        if (mounted) setState(() { _users = (res['data'] as List<dynamic>?) ?? []; _loading = false; });
      } else { if (mounted) setState(() => _loading = false); }
    } catch (e) { if (mounted) setState(() => _loading = false); }
  }

  void _showInviteDialog() {
    final emailCtrl = TextEditingController();
    String role = 'operator';
    showDialog(context: context, builder: (ctx) => AlertDialog(
      title: const Text('Invitar usuario'),
      content: Column(mainAxisSize: MainAxisSize.min, children: [
        TextField(controller: emailCtrl, decoration: const InputDecoration(labelText: 'Email'), keyboardType: TextInputType.emailAddress),
        const SizedBox(height: 12),
        DropdownButtonFormField<String>(value: role, decoration: const InputDecoration(labelText: 'Rol'),
          items: const [DropdownMenuItem(value: 'admin', child: Text('Admin')), DropdownMenuItem(value: 'operator', child: Text('Operador'))],
          onChanged: (v) => role = v ?? 'operator'),
      ]),
      actions: [
        TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancelar')),
        ElevatedButton(onPressed: () async {
          try {
            final api = context.read<AuthProvider>().api;
            await api.post('/tenants/${context.read<AuthProvider>().selectedTenant!['id']}/users', data: {'email': emailCtrl.text, 'role': role});
            if (mounted) { Navigator.pop(ctx); _load(); ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Usuario invitado'), backgroundColor: AppColors.success)); }
          } catch (e) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e'))); }
        }, child: const Text('Invitar')),
      ],
    ));
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: l.translate('user.title'),
      currentRoute: 'users',
      floatingActionButton: FloatingActionButton(backgroundColor: AppColors.primary, onPressed: _showInviteDialog, child: const Icon(Icons.person_add, color: Colors.white)),
      body: _loading ? const Center(child: CircularProgressIndicator())
          : _users.isEmpty ? EmptyState(icon: Icons.group_outlined, actionLabel: l.translate('user.invite'), onAction: _showInviteDialog)
          : RefreshIndicator(onRefresh: _load, child: ListView.builder(
              padding: const EdgeInsets.all(16), itemCount: _users.length,
              itemBuilder: (_, i) {
                final u = _users[i];
                return Card(margin: const EdgeInsets.only(bottom: 8), child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Row(children: [
                    CircleAvatar(radius: 20, backgroundColor: AppColors.primaryBg,
                      child: Text(((u['name'] as String? ?? '?')[0]).toUpperCase(), style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary))),
                    const SizedBox(width: 12),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(u['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600)),
                      Text(u['email'] ?? '', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                    ])),
                    Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3), decoration: BoxDecoration(
                      color: u['role'] == 'admin' ? AppColors.primaryBg : AppColors.warningBg, borderRadius: BorderRadius.circular(12)),
                      child: Text(u['role'] == 'admin' ? 'Admin' : 'Operador', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: u['role'] == 'admin' ? AppColors.primary : AppColors.warning))),
                  ]),
                ));
              },
            )),
    );
  }
}
