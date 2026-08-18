import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

class AppLocalizations {
  final Locale locale;

  AppLocalizations(this.locale);

  static AppLocalizations of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations)!;
  }

  static const LocalizationsDelegate<AppLocalizations> delegate = _AppLocalizationsDelegate();

  Map<String, String>? _localizedStrings;

  Future<bool> load() async {
    final lang = locale.languageCode;
    final json = lang == 'en' ? _enStrings : _esStrings;
    _localizedStrings = json;
    return true;
  }

  String translate(String key, {Map<String, String>? params}) {
    final text = _localizedStrings?[key] ?? key;
    if (params != null) {
      return params.entries.fold(text, (acc, e) => acc.replaceAll('{{${e.key}}}', e.value));
    }
    return text;
  }

  static Map<String, String> get _esStrings => {
    'app.name': 'HospedaCheck',
    'app.tagline': 'Gestión de visitantes',

    'nav.dashboard': 'Dashboard',
    'nav.reservations': 'Reservas',
    'nav.properties': 'Alojamientos',
    'nav.guests': 'Huéspedes',
    'nav.checkins': 'Check-ins',
    'nav.integrations': 'Integraciones',
    'nav.ses': 'SES Hospedajes',
    'nav.billing': 'Facturación',
    'nav.users': 'Usuarios',
    'nav.settings': 'Ajustes',
    'nav.audit': 'Auditoría',
    'nav.admin': 'Admin',
    'nav.tenants': 'Empresas',
    'nav.logout': 'Cerrar sesión',

    'auth.login': 'Iniciar sesión',
    'auth.email': 'Email',
    'auth.password': 'Contraseña',
    'auth.remember': 'Recordarme',
    'auth.forgot_password': '¿Olvidó su contraseña?',
    'auth.register': 'Registrarse',
    'auth.no_account': '¿No tiene cuenta?',
    'auth.login_error': 'Credenciales incorrectas',
    'auth.logout_success': 'Sesión cerrada',

    'common.save': 'Guardar',
    'common.cancel': 'Cancelar',
    'common.delete': 'Eliminar',
    'common.edit': 'Editar',
    'common.create': 'Crear',
    'common.search': 'Buscar',
    'common.filter': 'Filtrar',
    'common.loading': 'Cargando...',
    'common.no_data': 'No hay datos',
    'common.confirm': 'Confirmar',
    'common.back': 'Volver',
    'common.actions': 'Acciones',
    'common.status': 'Estado',
    'common.name': 'Nombre',
    'common.email': 'Email',
    'common.phone': 'Teléfono',
    'common.type': 'Tipo',
    'common.date': 'Fecha',
    'common.amount': 'Importe',
    'common.yes': 'Sí',
    'common.no': 'No',
    'common.retry': 'Reintentar',
    'common.send': 'Enviar',

    'dashboard.title': 'Panel de control',
    'dashboard.today_reservations': 'Reservas de hoy',
    'dashboard.checkins_pending': 'Check-ins pendientes',
    'dashboard.active_guests': 'Huéspedes activos',
    'dashboard.upcoming_arrivals': 'Próximas llegadas',
    'dashboard.quick_actions': 'Acciones rápidas',
    'dashboard.search_reservation': 'Buscar reserva',
    'dashboard.quick_checkin': 'Check-in rápido',
    'dashboard.properties': 'Alojamientos',
    'dashboard.no_reservations': 'No hay reservas para hoy',

    'reservation.title': 'Reservas',
    'reservation.create': 'Nueva reserva',
    'reservation.detail': 'Detalle de reserva',
    'reservation.guest_name': 'Nombre del huésped',
    'reservation.checkin_date': 'Fecha entrada',
    'reservation.checkout_date': 'Fecha salida',
    'reservation.adults': 'Adultos',
    'reservation.children': 'Niños',
    'reservation.property': 'Alojamiento',
    'reservation.source': 'Origen',
    'reservation.notes': 'Notas',
    'reservation.send_checkin': 'Enviar check-in',
    'reservation.guests': 'Huéspedes',
    'reservation.status_confirmed': 'Confirmada',
    'reservation.status_pending': 'Pendiente',
    'reservation.status_completed': 'Completada',
    'reservation.status_cancelled': 'Cancelada',
    'reservation.status_checkin_sent': 'Check-in enviado',
    'reservation.status_partial': 'Parcial',

    'property.title': 'Alojamientos',
    'property.create': 'Nuevo alojamiento',
    'property.detail': 'Detalle del alojamiento',
    'property.name': 'Nombre',
    'property.type': 'Tipo',
    'property.address': 'Dirección',
    'property.city': 'Ciudad',
    'property.capacity': 'Capacidad',
    'property.license': 'Nº licencia',
    'property.checkin_time': 'Hora entrada',
    'property.checkout_time': 'Hora salida',
    'property.type_apartment': 'Apartamento',
    'property.type_house': 'Casa',
    'property.type_villa': 'Villa',
    'property.type_studio': 'Estudio',
    'property.type_hotel': 'Hotel',
    'property.type_rural': 'Casa rural',
    'property.type_other': 'Otro',

    'guest.title': 'Huéspedes',
    'guest.detail': 'Detalle del huésped',
    'guest.first_name': 'Nombre',
    'guest.last_name': 'Apellido 1',
    'guest.last_name2': 'Segundo apellido',
    'guest.document_type': 'Tipo documento',
    'guest.document_number': 'Nº documento',
    'guest.nationality': 'Nacionalidad',
    'guest.birth_date': 'Fecha nacimiento',
    'guest.dni': 'DNI',
    'guest.nie': 'NIE',
    'guest.passport': 'Pasaporte',
    'guest.other': 'Otro',

    'checkin.title': 'Check-ins',
    'checkin.detail': 'Detalle del check-in',
    'checkin.verify': 'Verificar',
    'checkin.reject': 'Rechazar',
    'checkin.presential': 'Check-in presencial',
    'checkin.scan_document': 'Escanear DNI / Pasaporte',
    'checkin.capture_signature': 'Capturar firma',
    'checkin.status_completed': 'Completado',
    'checkin.status_pending': 'Pendiente',
    'checkin.status_verified': 'Verificado',
    'checkin.status_rejected': 'Rechazado',

    'integration.title': 'Integraciones',
    'integration.connect_booking': 'Conectar Booking',
    'integration.connect_airbnb': 'Conectar Airbnb',
    'integration.import_ics': 'Importar ICS',
    'integration.calendars': 'Calendarios',

    'ses.title': 'SES Hospedajes',
    'ses.prepare': 'Preparar envío',
    'ses.send': 'Enviar',
    'ses.retry': 'Reintentar',
    'ses.export': 'Exportar',
    'ses.submissions': 'Envíos',

    'billing.title': 'Facturación',
    'billing.subscription': 'Suscripción',
    'billing.invoices': 'Facturas',
    'billing.change_plan': 'Cambiar plan',
    'billing.current_plan': 'Plan actual',
    'billing.trial': 'Periodo de prueba',
    'billing.next_payment': 'Próximo pago',

    'user.title': 'Usuarios',
    'user.invite': 'Invitar usuario',
    'user.role': 'Rol',
    'user.admin': 'Admin',
    'user.operator': 'Operador',

    'setting.title': 'Ajustes',
    'setting.company': 'Datos de la empresa',
    'setting.language': 'Idioma',

    'audit.title': 'Auditoría',
    'audit.activity': 'Actividad',

    'admin.title': 'Administración',
    'admin.dashboard': 'Panel admin',
    'admin.tenants': 'Empresas',
    'admin.stats': 'Estadísticas',
    'admin.logs': 'Registros',

    'error.generic': 'Ha ocurrido un error',
    'error.network': 'Error de conexión',
    'error.unauthorized': 'No autorizado',
    'error.not_found': 'No encontrado',
    'error.server': 'Error del servidor',
    'error.timeout': 'Tiempo de espera agotado',
  };

  static Map<String, String> get _enStrings => {
    'app.name': 'HospedaCheck',
    'app.tagline': 'Visitor Management',

    'nav.dashboard': 'Dashboard',
    'nav.reservations': 'Reservations',
    'nav.properties': 'Properties',
    'nav.guests': 'Guests',
    'nav.checkins': 'Check-ins',
    'nav.integrations': 'Integrations',
    'nav.ses': 'SES Reports',
    'nav.billing': 'Billing',
    'nav.users': 'Users',
    'nav.settings': 'Settings',
    'nav.audit': 'Audit',
    'nav.admin': 'Admin',
    'nav.tenants': 'Companies',
    'nav.logout': 'Log out',

    'auth.login': 'Sign in',
    'auth.email': 'Email',
    'auth.password': 'Password',
    'auth.remember': 'Remember me',
    'auth.forgot_password': 'Forgot password?',
    'auth.register': 'Register',
    'auth.no_account': 'Don\'t have an account?',
    'auth.login_error': 'Invalid credentials',
    'auth.logout_success': 'Session closed',

    'common.save': 'Save',
    'common.cancel': 'Cancel',
    'common.delete': 'Delete',
    'common.edit': 'Edit',
    'common.create': 'Create',
    'common.search': 'Search',
    'common.filter': 'Filter',
    'common.loading': 'Loading...',
    'common.no_data': 'No data',
    'common.confirm': 'Confirm',
    'common.back': 'Back',
    'common.actions': 'Actions',
    'common.status': 'Status',
    'common.name': 'Name',
    'common.email': 'Email',
    'common.phone': 'Phone',
    'common.type': 'Type',
    'common.date': 'Date',
    'common.amount': 'Amount',
    'common.yes': 'Yes',
    'common.no': 'No',
    'common.retry': 'Retry',
    'common.send': 'Send',

    'dashboard.title': 'Dashboard',
    'dashboard.today_reservations': 'Today\'s reservations',
    'dashboard.checkins_pending': 'Pending check-ins',
    'dashboard.active_guests': 'Active guests',
    'dashboard.upcoming_arrivals': 'Upcoming arrivals',
    'dashboard.quick_actions': 'Quick actions',
    'dashboard.search_reservation': 'Search reservation',
    'dashboard.quick_checkin': 'Quick check-in',
    'dashboard.properties': 'Properties',
    'dashboard.no_reservations': 'No reservations for today',

    'reservation.title': 'Reservations',
    'reservation.create': 'New reservation',
    'reservation.detail': 'Reservation detail',
    'reservation.guest_name': 'Guest name',
    'reservation.checkin_date': 'Check-in date',
    'reservation.checkout_date': 'Check-out date',
    'reservation.adults': 'Adults',
    'reservation.children': 'Children',
    'reservation.property': 'Property',
    'reservation.source': 'Source',
    'reservation.notes': 'Notes',
    'reservation.send_checkin': 'Send check-in',
    'reservation.guests': 'Guests',
    'reservation.status_confirmed': 'Confirmed',
    'reservation.status_pending': 'Pending',
    'reservation.status_completed': 'Completed',
    'reservation.status_cancelled': 'Cancelled',
    'reservation.status_checkin_sent': 'Check-in sent',
    'reservation.status_partial': 'Partial',

    'property.title': 'Properties',
    'property.create': 'New property',
    'property.detail': 'Property detail',
    'property.name': 'Name',
    'property.type': 'Type',
    'property.address': 'Address',
    'property.city': 'City',
    'property.capacity': 'Capacity',
    'property.license': 'License #',
    'property.checkin_time': 'Check-in time',
    'property.checkout_time': 'Check-out time',
    'property.type_apartment': 'Apartment',
    'property.type_house': 'House',
    'property.type_villa': 'Villa',
    'property.type_studio': 'Studio',
    'property.type_hotel': 'Hotel',
    'property.type_rural': 'Rural house',
    'property.type_other': 'Other',

    'guest.title': 'Guests',
    'guest.detail': 'Guest detail',
    'guest.first_name': 'First name',
    'guest.last_name': 'Last name',
    'guest.last_name2': 'Second surname',
    'guest.document_type': 'Document type',
    'guest.document_number': 'Document #',
    'guest.nationality': 'Nationality',
    'guest.birth_date': 'Birth date',
    'guest.dni': 'DNI',
    'guest.nie': 'NIE',
    'guest.passport': 'Passport',
    'guest.other': 'Other',

    'checkin.title': 'Check-ins',
    'checkin.detail': 'Check-in detail',
    'checkin.verify': 'Verify',
    'checkin.reject': 'Reject',
    'checkin.presential': 'In-person check-in',
    'checkin.scan_document': 'Scan ID / Passport',
    'checkin.capture_signature': 'Capture signature',
    'checkin.status_completed': 'Completed',
    'checkin.status_pending': 'Pending',
    'checkin.status_verified': 'Verified',
    'checkin.status_rejected': 'Rejected',

    'integration.title': 'Integrations',
    'integration.connect_booking': 'Connect Booking',
    'integration.connect_airbnb': 'Connect Airbnb',
    'integration.import_ics': 'Import ICS',
    'integration.calendars': 'Calendars',

    'ses.title': 'SES Reports',
    'ses.prepare': 'Prepare submission',
    'ses.send': 'Send',
    'ses.retry': 'Retry',
    'ses.export': 'Export',
    'ses.submissions': 'Submissions',

    'billing.title': 'Billing',
    'billing.subscription': 'Subscription',
    'billing.invoices': 'Invoices',
    'billing.change_plan': 'Change plan',
    'billing.current_plan': 'Current plan',
    'billing.trial': 'Trial period',
    'billing.next_payment': 'Next payment',

    'user.title': 'Users',
    'user.invite': 'Invite user',
    'user.role': 'Role',
    'user.admin': 'Admin',
    'user.operator': 'Operator',

    'setting.title': 'Settings',
    'setting.company': 'Company data',
    'setting.language': 'Language',

    'audit.title': 'Audit',
    'audit.activity': 'Activity',

    'admin.title': 'Administration',
    'admin.dashboard': 'Admin panel',
    'admin.tenants': 'Companies',
    'admin.stats': 'Statistics',
    'admin.logs': 'Logs',

    'error.generic': 'An error occurred',
    'error.network': 'Connection error',
    'error.unauthorized': 'Unauthorized',
    'error.not_found': 'Not found',
    'error.server': 'Server error',
    'error.timeout': 'Connection timeout',
  };
}

class _AppLocalizationsDelegate extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  bool isSupported(Locale locale) => ['es', 'en'].contains(locale.languageCode);

  @override
  Future<AppLocalizations> load(Locale locale) async {
    final localizations = AppLocalizations(locale);
    await localizations.load();
    return localizations;
  }

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}
