import 'package:flutter/material.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:provider/provider.dart';
import 'core/api/api_client.dart';
import 'core/storage/auth_storage.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/providers/auth_provider.dart';
import 'features/auth/screens/login_screen.dart';
import 'features/home/screens/dashboard_screen.dart';
import 'features/checkin/screens/presential_checkin_screen.dart';
import 'features/tenants/screens/tenant_selector_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('es', null);
  runApp(const CheckInApp());
}

class CheckInApp extends StatelessWidget {
  const CheckInApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider(
          ApiClient(),
          AuthStorage(),
        )),
      ],
      child: MaterialApp(
        title: 'CheckIn',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.lightTheme,
        initialRoute: '/',
        routes: {
          '/': (context) => const AuthGate(),
          '/tenants/select': (context) => const TenantSelectorScreen(),
          '/home': (context) => const DashboardScreen(),
          '/checkin/presential': (context) => const PresentialCheckinScreen(),
        },
        onGenerateRoute: (settings) {
          if (settings.name == '/reservations/detail') {
            return MaterialPageRoute(
              builder: (context) => const DashboardScreen(),
              settings: settings,
            );
          }
          return null;
        },
      ),
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
          return const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          );
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
