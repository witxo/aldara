import 'package:flutter/foundation.dart';
import 'dart:convert';
import '../../../core/api/api_client.dart';
import '../../../core/storage/auth_storage.dart';

class AuthProvider extends ChangeNotifier {
  final ApiClient _api;
  final AuthStorage _storage;

  AuthProvider(this._api, this._storage) {
    _loadSavedSession();
  }

  bool _isLoading = true;
  bool _isAuthenticated = false;
  bool _needsTenantSelection = false;
  String? _token;
  Map<String, dynamic>? _user;
  List<Map<String, dynamic>> _tenants = [];
  Map<String, dynamic>? _selectedTenant;
  String? _error;

  bool get isLoading => _isLoading;
  bool get isAuthenticated => _isAuthenticated;
  bool get needsTenantSelection => _needsTenantSelection;
  String? get token => _token;
  Map<String, dynamic>? get user => _user;
  List<Map<String, dynamic>> get tenants => _tenants;
  Map<String, dynamic>? get selectedTenant => _selectedTenant;
  String? get error => _error;
  ApiClient get api => _api;

  Future<void> _loadSavedSession() async {
    try {
      _token = await _storage.getToken();
      final userData = await _storage.getUserData();
      final tenantId = await _storage.getTenantId();

      if (_token != null && userData != null) {
        _api.setToken(_token);
        _user = jsonDecode(userData);
        if (tenantId != null) {
          _selectedTenant = {'id': tenantId};
          _api.setTenantId(tenantId);
        } else {
          _needsTenantSelection = true;
        }
        _isAuthenticated = true;
      }
    } catch (e) {
      debugPrint('Error loading session: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.post('/auth/login', data: {
        'email': email,
        'password': password,
        'device_name': 'mobile',
      });

      final data = response['data'] as Map<String, dynamic>;
      _token = data['token'] as String?;
      _user = data['user'] as Map<String, dynamic>?;
      _tenants = List<Map<String, dynamic>>.from(data['tenants'] ?? []);

      if (_token != null) {
        await _storage.saveToken(_token!);
        await _storage.saveUserData(jsonEncode(_user));
        _api.setToken(_token);

        _isAuthenticated = true;

        if (_tenants.length == 1) {
          await selectTenant(_tenants.first['id'] as int);
          _needsTenantSelection = false;
        } else if (_tenants.length > 1) {
          _needsTenantSelection = true;
        }
      }

      _isLoading = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Credenciales incorrectas';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> register(String name, String email, String password, String passwordConfirmation) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.post('/register', data: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
      });

      final data = response['data'] as Map<String, dynamic>?;
      if (data != null && data['token'] != null) {
        _token = data['token'] as String;
        _user = data['user'] as Map<String, dynamic>?;
        await _storage.saveToken(_token!);
        await _storage.saveUserData(jsonEncode(_user));
        _api.setToken(_token);
        _isAuthenticated = true;
      }

      _isLoading = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Error al registrarse';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> sendForgotPassword(String email) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      await _api.post('/auth/forgot-password', data: {'email': email});
      _isLoading = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Error al enviar email';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> selectTenant(int tenantId) async {
    _selectedTenant = {'id': tenantId};
    _api.setTenantId(tenantId);
    await _storage.saveTenantId(tenantId);
    _needsTenantSelection = false;
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await _api.post('/auth/logout');
    } catch (_) {}
    await _storage.clear();
    _token = null;
    _user = null;
    _tenants = [];
    _selectedTenant = null;
    _isAuthenticated = false;
    _error = null;
    _api.setToken(null);
    _api.setTenantId(null);
    notifyListeners();
  }
}
