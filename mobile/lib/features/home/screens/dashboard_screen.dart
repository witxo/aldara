import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/status_badge.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/loading_overlay.dart';
import '../../../core/widgets/empty_state.dart';
import '../../auth/providers/auth_provider.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  List<dynamic> _todayReservations = [];
  bool _loading = true;
  int _totalReservations = 0;
  int _checkinsPending = 0;
  int _activeGuests = 0;
  String? _error;

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

      final resResponse = await api.get('/reservations', queryParameters: {
        'checkin_date_from': today,
        'checkin_date_to': today,
        'per_page': '20',
        'sort': '-created_at',
      });

      final checkinResponse = await api.get('/checkins', queryParameters: {
        'per_page': '10',
        'sort': '-created_at',
      });

      if (mounted) {
        setState(() {
          _todayReservations = (resResponse['data'] as List<dynamic>?) ?? [];
          _totalReservations = (resResponse['meta']?['total'] as int?) ?? _todayReservations.length;
          _checkinsPending = ((checkinResponse['data'] as List<dynamic>?) ?? [])
              .where((c) => c['status'] == 'pending' || c['status'] == 'in_progress').length;
          _activeGuests = ((checkinResponse['data'] as List<dynamic>?) ?? [])
              .where((c) => c['status'] == 'completed' || c['status'] == 'verified').length;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() { _loading = false; _error = e.toString(); });
    }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: l.translate('dashboard.title'),
      currentRoute: 'dashboard',
      body: _loading
        ? const Center(child: CircularProgressIndicator())
        : RefreshIndicator(
            onRefresh: _loadData,
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                _buildStatsRow(),
                const SizedBox(height: 24),
                Text(l.translate('dashboard.today_reservations'), style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                const SizedBox(height: 12),
                if (_todayReservations.isEmpty)
                  EmptyState(icon: Icons.event_busy, message: l.translate('dashboard.no_reservations'))
                else
                  ..._todayReservations.map((r) => _buildReservationCard(r)),
                const SizedBox(height: 24),
                Text(l.translate('dashboard.quick_actions'), style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(child: _buildActionCard(Icons.search, l.translate('dashboard.search_reservation'), () => Navigator.pushNamed(context, '/reservations'))),
                    const SizedBox(width: 12),
                    Expanded(child: _buildActionCard(Icons.edit_note, l.translate('dashboard.quick_checkin'), () => Navigator.pushNamed(context, '/checkin/presential'))),
                  ],
                ),
              ],
            ),
          ),
    );
  }

  Widget _buildStatsRow() {
    return Row(
      children: [
        Expanded(child: StatCardWidget(label: 'Hoy', value: '$_totalReservations', icon: Icons.calendar_today, color: AppColors.primary)),
        const SizedBox(width: 8),
        Expanded(child: StatCardWidget(label: 'Pendientes', value: '$_checkinsPending', icon: Icons.pending, color: AppColors.warning)),
        const SizedBox(width: 8),
        Expanded(child: StatCardWidget(label: 'Activos', value: '$_activeGuests', icon: Icons.people, color: AppColors.success)),
      ],
    );
  }

  Widget _buildActionCard(IconData icon, String label, VoidCallback onTap) {
    return Card(
      margin: EdgeInsets.zero,
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            children: [
              Icon(icon, size: 28, color: AppColors.primary),
              const SizedBox(height: 8),
              Text(label, textAlign: TextAlign.center, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildReservationCard(dynamic r) {
    final checkin = r['checkin_date'] != null ? DateTime.tryParse('${r['checkin_date']}T00:00:00') : null;
    final checkout = r['checkout_date'] != null ? DateTime.tryParse('${r['checkout_date']}T00:00:00') : null;

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => Navigator.pushNamed(context, '/reservations/detail', arguments: r),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              Container(
                width: 44, height: 44,
                decoration: BoxDecoration(color: AppColors.primaryBg, borderRadius: BorderRadius.circular(10)),
                child: Center(child: Text(
                  (r['guest_name'] as String? ?? '?')[0].toUpperCase(),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.primary),
                )),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(r['guest_name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 2),
                    if (checkin != null && checkout != null)
                      Text('${DateFormat('dd/MM').format(checkin)} - ${DateFormat('dd/MM').format(checkout)}',
                        style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                    Text('${r['adults'] ?? 0} adultos · ${r['children'] ?? 0} niños',
                      style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                  ],
                ),
              ),
              StatusBadge(r['status'] as String?),
            ],
          ),
        ),
      ),
    );
  }
}

class StatCardWidget extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color color;

  const StatCardWidget({super.key, required this.label, required this.value, required this.icon, required this.color});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, size: 18, color: color),
            const SizedBox(height: 8),
            Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
            Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
          ],
        ),
      ),
    );
  }
}
