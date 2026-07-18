import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

class ApiClient {
  late final Dio _dio;
  String? _token;
  int? _tenantId;

  ApiClient() {
    _dio = Dio(BaseOptions(
      baseUrl: const String.fromEnvironment(
        'API_URL',
        defaultValue: 'https://aldara.ivema.es/api/v1',
      ),
      connectTimeout: const Duration(seconds: 10),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) {
        if (_token != null) {
          options.headers['Authorization'] = 'Bearer $_token';
        }
        if (_tenantId != null) {
          options.headers['X-Tenant-Id'] = '$_tenantId';
        }
        debugPrint('[API] ${options.method} ${options.path}');
        handler.next(options);
      },
      onError: (error, handler) {
        debugPrint('[API Error] ${error.response?.statusCode}: ${error.message}');
        handler.next(error);
      },
    ));
  }

  void setToken(String? token) => _token = token;
  void setTenantId(int? id) => _tenantId = id;

  Future<Response> get(String path, {Map<String, dynamic>? queryParameters}) =>
      _dio.get(path, queryParameters: queryParameters);

  Future<Response> post(String path, {dynamic data}) =>
      _dio.post(path, data: data);

  Future<Response> put(String path, {dynamic data}) =>
      _dio.put(path, data: data);

  Future<Response> delete(String path) =>
      _dio.delete(path);
}
