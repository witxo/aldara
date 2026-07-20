import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/status_badge.dart';
import '../../../core/widgets/empty_state.dart';
import '../../guests/screens/guest_form_screen.dart';
import '../../auth/providers/auth_provider.dart';

class ReservationDetailScreen extends StatefulWidget {
  final dynamic reservation;
  const ReservationDetailScreen({super.key, required this.reservation});

  @override
  State<ReservationDetailScreen> createState() => _ReservationDetailScreenState();
}

class _ReservationDetailScreenState extends State<ReservationDetailScreen> {
  late Map<String, dynamic>? _r;
  List<dynamic> _guests = [];
  bool _loading = true;
  bool _sending = false;

  @override
  void initState() {
    super.initState();
    _r = widget.reservation as Map<String, dynamic>?;
    _loadDetail();
  }

  Future<void> _loadDetail() async {
    if (_r == null) return;
    setState(() => _loading = true);
    try {
      final api = context.read<AuthProvider>().api;
      final res = await api.get('/reservations/${_r!['id']}');
      final guestsRes = await api.get('/reservations/${_r!['id']}/guests');
      if (mounted) setState(() {
        _r = res['data'] as Map<String, dynamic>?;
        _guests = (guestsRes['data'] as List<dynamic>?) ?? [];
        _loading = false;
      });
    } catch (e) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _sendCheckin() async {
    if (_r == null) return;
    setState(() => _sending = true);
    try {
      final api = context.read<AuthProvider>().api;
      await api.post('/reservations/${_r!['id']}/send-checkin');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Check-in enviado'), backgroundColor: AppColors.success));
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: _r?['guest_name'] ?? '',
      currentRoute: 'reservations',
      body: _loading
        ? const Center(child: CircularProgressIndicator())
        : _r == null
          ? EmptyState()
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Card(
                  margin: const EdgeInsets.only(bottom: 16),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(children: [
                          Expanded(child: Text(_r!['guest_name'] ?? '', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold))),
                          StatusBadge(_r!['status'] as String?),
                        ]),
                        const SizedBox(height: 16),
                        _field('Código', _r!['code'] ?? '-'),
                        _field('Email', _r!['guest_email'] ?? '-'),
                        _field('Teléfono', _r!['guest_phone'] ?? '-'),
                        const Divider(height: 24),
                        _field(l.translate('reservation.checkin_date'), _fmt(_r!['checkin_date'])),
                        _field(l.translate('reservation.checkout_date'), _fmt(_r!['checkout_date'])),
                        _field(l.translate('reservation.adults'), '${_r!['adults'] ?? 0}'),
                        _field(l.translate('reservation.children'), '${_r!['children'] ?? 0}'),
                        if (_r!['infants'] != null) _field('Bebés', '${_r!['infants']}'),
                        if (_r!['total_amount'] != null) _field(l.translate('common.amount'), '${_r!['total_amount']} ${_r!['currency'] ?? '€'}'),
                        if (_r!['source'] != null) _field(l.translate('reservation.source'), _r!['source']),
                      ],
                    ),
                  ),
                ),
                if (_r!['notes'] != null && (_r!['notes'] as String).isNotEmpty)
                  Card(
                    margin: const EdgeInsets.only(bottom: 16),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(l.translate('reservation.notes'), style: const TextStyle(fontWeight: FontWeight.bold)),
                          const SizedBox(height: 8),
                          Text(_r!['notes'], style: const TextStyle(color: AppColors.textSecondary)),
                        ],
                      ),
                    ),
                  ),
                Card(
                  margin: const EdgeInsets.only(bottom: 16),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(children: [
                          Text('${l.translate('reservation.guests')} (${_guests.length})', style: const TextStyle(fontWeight: FontWeight.bold)),
                          const Spacer(),
                          TextButton.icon(
                            onPressed: () async {
                              final result = await Navigator.push<bool>(
                                context,
                                MaterialPageRoute(builder: (_) => GuestFormScreen(reservationId: _r!['id'] as int?)),
                              );
                              if (result == true) _loadDetail();
                            },
                            icon: const Icon(Icons.add, size: 16),
                            label: const Text('Añadir', style: TextStyle(fontSize: 13)),
                            style: TextButton.styleFrom(padding: const EdgeInsets.symmetric(horizontal: 8), minimumSize: Size.zero, tapTargetSize: MaterialTapTargetSize.shrinkWrap),
                          ),
                        ]),
                        const SizedBox(height: 8),
                        ...(_guests.isNotEmpty
                          ? _guests.map((g) => Padding(
                              padding: const EdgeInsets.only(bottom: 8),
                              child: Row(children: [
                                const Icon(Icons.person, size: 16, color: AppColors.textSecondary),
                                const SizedBox(width: 8),
                                Expanded(child: Text('${g['first_name'] ?? ''} ${g['last_name'] ?? ''}', style: const TextStyle(fontSize: 13))),
                                if (g['is_main_guest'] == true)
                                  Container(margin: const EdgeInsets.only(right: 4), padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1), decoration: BoxDecoration(color: AppColors.primaryBg, borderRadius: BorderRadius.circular(4)), child: const Text('Ppal', style: TextStyle(fontSize: 10, color: AppColors.primary))),
                                PopupMenuButton<String>(
                                  onSelected: (v) async {
                                    if (v == 'edit') {
                                      final result = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => GuestFormScreen(guest: g)));
                                      if (result == true) _loadDetail();
                                    } else if (v == 'delete') {
                                      final confirm = await showDialog<bool>(context: context, builder: (ctx) => AlertDialog(title: const Text('Eliminar huésped'), content: const Text('¿Está seguro?'), actions: [TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')), TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Eliminar', style: TextStyle(color: Colors.red)))]));
                                      if (confirm == true) {
                                        try {
                                          await context.read<AuthProvider>().api.delete('/guests/${g['id']}');
                                          _loadDetail();
                                        } catch (e) {
                                          if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                                        }
                                      }
                                    }
                                  },
                                  itemBuilder: (_) => [
                                    const PopupMenuItem(value: 'edit', child: Row(children: [Icon(Icons.edit, size: 16), SizedBox(width: 8), Text('Editar')])),
                                    const PopupMenuItem(value: 'delete', child: Row(children: [Icon(Icons.delete, size: 16, color: Colors.red), SizedBox(width: 8), Text('Eliminar', style: TextStyle(color: Colors.red))])),
                                  ],
                                ),
                              ]),
                            ))
                          : [Padding(padding: const EdgeInsets.symmetric(vertical: 8), child: Text(l.translate('common.no_data'), style: const TextStyle(fontSize: 13, color: AppColors.textMuted)))]),
                      ],
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: ElevatedButton.icon(
                    onPressed: _sending ? null : _sendCheckin,
                    icon: _sending ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Icon(Icons.send, size: 16),
                    label: Text(l.translate('reservation.send_checkin')),
                    style: ElevatedButton.styleFrom(fixedSize: const Size.fromHeight(44)),
                  ),
                ),
              ],
            ),
    );
  }

  Widget _field(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        children: [
          SizedBox(width: 120, child: Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13))),
          Expanded(child: Text(value, style: const TextStyle(fontSize: 13))),
        ],
      ),
    );
  }

  String _fmt(String? d) => d != null ? DateFormat('dd/MM/yyyy').format(DateTime.parse(d)) : '-';
}
