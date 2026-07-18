import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class AuthStorage {
  static const _tokenKey = 'auth_token';
  static const _tenantIdKey = 'current_tenant_id';
  static const _userDataKey = 'user_data';

  final _storage = const FlutterSecureStorage();

  Future<void> saveToken(String token) async =>
      await _storage.write(key: _tokenKey, value: token);

  Future<String?> getToken() async =>
      await _storage.read(key: _tokenKey);

  Future<void> saveTenantId(int id) async =>
      await _storage.write(key: _tenantIdKey, value: id.toString());

  Future<int?> getTenantId() async {
    final value = await _storage.read(key: _tenantIdKey);
    return value != null ? int.tryParse(value) : null;
  }

  Future<void> saveUserData(String json) async =>
      await _storage.write(key: _userDataKey, value: json);

  Future<String?> getUserData() async =>
      await _storage.read(key: _userDataKey);

  Future<void> clear() async =>
      await _storage.deleteAll();
}
