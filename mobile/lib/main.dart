import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:provider/provider.dart';
import 'core/api/api_client.dart';
import 'core/storage/auth_storage.dart';
import 'core/theme/app_theme.dart';
import 'core/i18n/app_localizations.dart';
import 'features/auth/providers/auth_provider.dart';
import 'features/auth/screens/login_screen.dart';
import 'features/auth/screens/register_screen.dart';
import 'features/auth/screens/forgot_password_screen.dart';
import 'features/home/screens/dashboard_screen.dart';
import 'features/reservations/screens/reservation_list_screen.dart';
import 'features/reservations/screens/reservation_detail_screen.dart';
import 'features/reservations/screens/reservation_form_screen.dart';
import 'features/properties/screens/property_list_screen.dart';
import 'features/properties/screens/property_detail_screen.dart';
import 'features/properties/screens/property_form_screen.dart';
import 'features/guests/screens/guest_list_screen.dart';
import 'features/guests/screens/guest_detail_screen.dart';
import 'features/checkins/screens/checkin_list_screen.dart';
import 'features/checkins/screens/checkin_detail_screen.dart';
import 'features/checkin/screens/presential_checkin_screen.dart';
import 'features/integrations/screens/integration_list_screen.dart';
import 'features/ses/screens/ses_list_screen.dart';
import 'features/billing/screens/billing_screen.dart';
import 'features/users/screens/user_list_screen.dart';
import 'features/settings/screens/settings_screen.dart';
import 'features/tenants/screens/tenant_selector_screen.dart';
import 'features/admin/screens/admin_dashboard_screen.dart';
import 'features/admin/screens/admin_tenant_list_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('es', null);
  runApp(const AldaraApp());
}

class AldaraApp extends StatelessWidget {
  const AldaraApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider(ApiClient(), AuthStorage())),
      ],
      child: Consumer<AuthProvider>(
        builder: (context, auth, _) {
          return MaterialApp(
            title: 'Aldara',
            debugShowCheckedModeBanner: false,
            theme: AppTheme.lightTheme,
            locale: Locale(auth.user?['language'] as String? ?? 'es'),
            supportedLocales: const [Locale('es'), Locale('en')],
            localizationsDelegates: [
              AppLocalizations.delegate,
              GlobalMaterialLocalizations.delegate,
              GlobalWidgetsLocalizations.delegate,
              GlobalCupertinoLocalizations.delegate,
            ],
            localeResolutionCallback: (locale, supported) {
              if (locale != null && supported.contains(locale)) return locale;
              return const Locale('es');
            },
            initialRoute: '/',
            onGenerateRoute: (settings) {
              final args = settings.arguments;

              switch (settings.name) {
                case '/':
                  return _pageRoute(AuthGate(), settings);
                case '/login':
                  return _pageRoute(const LoginScreen(), settings);
                case '/register':
                  return _pageRoute(const RegisterScreen(), settings);
                case '/forgot-password':
                  return _pageRoute(const ForgotPasswordScreen(), settings);
                case '/tenants/select':
                  return _pageRoute(const TenantSelectorScreen(), settings);
                case '/home':
                  return _pageRoute(const DashboardScreen(), settings);
                case '/reservations':
                  return _pageRoute(const ReservationListScreen(), settings);
                case '/reservations/create':
                  return _pageRoute(const ReservationFormScreen(), settings);
                case '/reservations/detail':
                  return _pageRoute(ReservationDetailScreen(reservation: args), settings);
                case '/properties':
                  return _pageRoute(const PropertyListScreen(), settings);
                case '/properties/create':
                  return _pageRoute(const PropertyFormScreen(), settings);
                case '/properties/detail':
                  return _pageRoute(PropertyDetailScreen(property: args), settings);
                case '/properties/edit':
                  return _pageRoute(PropertyFormScreen(property: args), settings);
                case '/guests':
                  return _pageRoute(const GuestListScreen(), settings);
                case '/guests/detail':
                  return _pageRoute(GuestDetailScreen(guest: args), settings);
                case '/checkins':
                  return _pageRoute(const CheckinListScreen(), settings);
                case '/checkins/detail':
                  return _pageRoute(CheckinDetailScreen(checkin: args), settings);
                case '/checkin/presential':
                  return _pageRoute(const PresentialCheckinScreen(), settings);
                case '/integrations':
                  return _pageRoute(const IntegrationListScreen(), settings);
                case '/ses':
                  return _pageRoute(const SesListScreen(), settings);
                case '/billing':
                  return _pageRoute(const BillingScreen(), settings);
                case '/users':
                  return _pageRoute(const UserListScreen(), settings);
                case '/settings':
                  return _pageRoute(const SettingsScreen(), settings);
                case '/admin':
                  return _pageRoute(const AdminDashboardScreen(), settings);
                case '/admin/tenants':
                  return _pageRoute(const AdminTenantListScreen(), settings);
                default:
                  return _pageRoute(const DashboardScreen(), settings);
              }
            },
          );
        },
      ),
    );
  }

  PageRouteBuilder _pageRoute(Widget page, RouteSettings settings) {
    return PageRouteBuilder(
      settings: settings,
      pageBuilder: (_, __, ___) => page,
      transitionDuration: const Duration(milliseconds: 200),
      transitionsBuilder: (_, animation, __, child) {
        return FadeTransition(opacity: animation, child: child);
      },
    );
  }
}

class AuthGate extends StatelessWidget {
  const AuthGate({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, auth, _) {
        if (auth.isLoading) {
          return const Scaffold(body: Center(child: CircularProgressIndicator()));
        }
        if (auth.isAuthenticated) {
          if (auth.needsTenantSelection) {
            return const TenantSelectorScreen();
          }
          return const DashboardScreen();
        }
        return const LoginScreen();
      },
    );
  }
}
