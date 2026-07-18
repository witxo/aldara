import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../auth/providers/auth_provider.dart';

class TenantSelectorScreen extends StatelessWidget {
  const TenantSelectorScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      appBar: AppBar(title: const Text('Seleccionar empresa')),
      body: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const SizedBox(height: 24),
          const Text(
            'Seleccione una empresa',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 32),
          ...auth.tenants.map((t) => Card(
            margin: const EdgeInsets.only(bottom: 12),
            child: ListTile(
              leading: CircleAvatar(
                backgroundColor: Colors.blue[100],
                child: Text(
                  (t['company_name'] as String)[0].toUpperCase(),
                  style: const TextStyle(color: Colors.blue, fontWeight: FontWeight.bold),
                ),
              ),
              title: Text(t['company_name'] ?? ''),
              subtitle: Text('Rol: ${t['role'] ?? ''} · ${t['status'] ?? ''}'),
              trailing: const Icon(Icons.chevron_right),
              onTap: () => auth.selectTenant(t['id']),
            ),
          )),
        ],
      ),
    );
  }
}
