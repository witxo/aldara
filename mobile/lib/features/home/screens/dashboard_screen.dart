import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../auth/providers/auth_provider.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  List<dynamic> _todayReservations = [];
  bool _loading = true;
  Map<String, int> _stats = {};

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final api = context.read<AuthProvider>().api;
      final today = DateFormat('yyyy-MM-dd').format(DateTime.now());

      final reservations = await api.get('/reservations', queryParameters: {
        'checkin_date_from': today,
        'checkin_date_to': today,
        'per_page': '20',
      });

      final stats = await api.get('/reservations', queryParameters: {
        'per_page': '1',
      });

      setState(() {
        _todayReservations = reservations.data['data'] ?? [];
        _loading = false;
      });
    } catch (e) {
      setState(() => _loading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error al cargar datos: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final tenantName = auth.selectedTenant?['company_name'] ?? 'CheckIn';

    return Scaffold(
      appBar: AppBar(
        title: Text(tenantName),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () => auth.logout(),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _buildDateHeader(),
                  const SizedBox(height: 16),
                  _buildStatsRow(),
                  const SizedBox(height: 24),
                  Text(
                    'Reservas de hoy',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  if (_todayReservations.isEmpty)
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(32),
                        child: Center(
                          child: Column(
                            children: [
                              Icon(Icons.event_busy, size: 48, color: Colors.grey[400]),
                              const SizedBox(height: 8),
                              Text('No hay reservas para hoy', style: TextStyle(color: Colors.grey[600])),
                            ],
                          ),
                        ),
                      ),
                    )
                  else
                    ..._todayReservations.map((r) => _buildReservationCard(r)),
                  const SizedBox(height: 24),
                  Text(
                    'Acciones rápidas',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: _buildQuickAction(
                          icon: Icons.search,
                          label: 'Buscar reserva',
                          onTap: () => _showSearchReservation(context),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildQuickAction(
                          icon: Icons.edit_note,
                          label: 'Check-in rápido',
                          onTap: () => Navigator.pushNamed(context, '/checkin/presential'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
      ),
    );
  }

  Widget _buildDateHeader() {
    final now = DateTime.now();
    final formatter = DateFormat("EEEE, d 'de' MMMM", 'es');
    return Row(
      children: [
        const Icon(Icons.calendar_today, size: 20, color: Colors.grey),
        const SizedBox(width: 8),
        Text(
          formatter.format(now),
          style: TextStyle(color: Colors.grey[600], fontSize: 16),
        ),
      ],
    );
  }

  Widget _buildStatsRow() {
    return Row(
      children: [
        _buildStatCard('Hoy', _todayReservations.length, Colors.blue),
        const SizedBox(width: 12),
        _buildStatCard('Entradas', _todayReservations.where((r) => r['status'] == 'confirmed').length, Colors.green),
        const SizedBox(width: 12),
        _buildStatCard('Pendientes', _todayReservations.where((r) => r['status'] == 'checkin_sent').length, Colors.orange),
      ],
    );
  }

  Widget _buildStatCard(String label, int count, Color color) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              Text(
                '$count',
                style: TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                  color: color,
                ),
              ),
              Text(label, style: TextStyle(color: Colors.grey[600], fontSize: 12)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildReservationCard(dynamic r) {
    final checkin = DateTime.parse(r['checkin_date']);
    final checkout = DateTime.parse(r['checkout_date']);
    final statusColors = {
      'confirmed': Colors.blue,
      'checkin_sent': Colors.orange,
      'completed': Colors.green,
      'cancelled': Colors.red,
    };

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        contentPadding: const EdgeInsets.all(12),
        title: Text(r['guest_name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text('${DateFormat('dd/MM').format(checkin)} - ${DateFormat('dd/MM').format(checkout)}'),
            Text('${r['adults']} adultos · ${r['children']} niños'),
          ],
        ),
        trailing: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: (statusColors[r['status']] ?? Colors.grey).withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                r['status']?.replaceAll('_', ' ') ?? '',
                style: TextStyle(
                  color: statusColors[r['status']] ?? Colors.grey,
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            const SizedBox(height: 4),
            TextButton.icon(
              onPressed: () => _startCheckin(r),
              icon: const Icon(Icons.check_circle, size: 16),
              label: const Text('Check-in', style: TextStyle(fontSize: 12)),
              style: TextButton.styleFrom(foregroundColor: Colors.blue, padding: const EdgeInsets.symmetric(horizontal: 8)),
            ),
          ],
        ),
        onTap: () => Navigator.pushNamed(context, '/reservations/detail', arguments: r),
      ),
    );
  }

  Widget _buildQuickAction({required IconData icon, required String label, required VoidCallback onTap}) {
    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            children: [
              Icon(icon, size: 32, color: const Color(0xFF2563EB)),
              const SizedBox(height: 8),
              Text(label, textAlign: TextAlign.center, style: const TextStyle(fontSize: 12)),
            ],
          ),
        ),
      ),
    );
  }

  void _showSearchReservation(BuildContext context) {
    showSearch(context: context, delegate: _ReservationSearchDelegate());
  }

  void _startCheckin(dynamic reservation) {
    Navigator.pushNamed(context, '/checkin/presential', arguments: reservation);
  }
}

class _ReservationSearchDelegate extends SearchDelegate {
  @override
  List<Widget>? buildActions(BuildContext context) => [
    IconButton(icon: const Icon(Icons.clear), onPressed: () => query = ''),
  ];

  @override
  Widget? buildLeading(BuildContext context) => IconButton(
    icon: const Icon(Icons.arrow_back),
    onPressed: () => close(context, null),
  );

  @override
  Widget buildResults(BuildContext context) => const Center(child: Text('Resultados'));

  @override
  Widget buildSuggestions(BuildContext context) => const Center(child: Text('Buscar por nombre o código'));
}
