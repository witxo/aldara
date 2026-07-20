import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, dynamic>? errors;

  ApiException(this.message, {this.statusCode, this.errors});

  @override
  String toString() => message;
}

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
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 30),
      sendTimeout: const Duration(seconds: 15),
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

  Future<Map<String, dynamic>> _handleResponse(Response response) async {
    final data = response.data is Map ? response.data as Map<String, dynamic> : <String, dynamic>{};
    if (response.statusCode! >= 200 && response.statusCode! < 300) {
      return data;
    }
    throw ApiException(
      data['message'] as String? ?? 'Error ${response.statusCode}',
      statusCode: response.statusCode,
      errors: data['errors'] as Map<String, dynamic>?,
    );
  }

  Future<Map<String, dynamic>> _handleError(DioException e) async {
    if (e.response != null) {
      final data = e.response!.data is Map ? e.response!.data as Map<String, dynamic> : <String, dynamic>{};
      throw ApiException(
        data['message'] as String? ?? 'Error ${e.response!.statusCode}',
        statusCode: e.response!.statusCode,
        errors: data['errors'] as Map<String, dynamic>?,
      );
    }
    if (e.type == DioExceptionType.connectionTimeout || e.type == DioExceptionType.sendTimeout || e.type == DioExceptionType.receiveTimeout) {
      throw ApiException('Tiempo de espera agotado', statusCode: 408);
    }
    if (e.type == DioExceptionType.connectionError) {
      throw ApiException('Error de conexión', statusCode: 0);
    }
    throw ApiException('Ha ocurrido un error', statusCode: 500);
  }

  Future<Map<String, dynamic>> get(String path, {Map<String, dynamic>? queryParameters}) async {
    try {
      final response = await _dio.get(path, queryParameters: queryParameters);
      return _handleResponse(response);
    } on DioException catch (e) {
      return _handleError(e);
    }
  }

  Future<Map<String, dynamic>> post(String path, {dynamic data}) async {
    try {
      final response = await _dio.post(path, data: data);
      return _handleResponse(response);
    } on DioException catch (e) {
      return _handleError(e);
    }
  }

  Future<Map<String, dynamic>> put(String path, {dynamic data}) async {
    try {
      final response = await _dio.put(path, data: data);
      return _handleResponse(response);
    } on DioException catch (e) {
      return _handleError(e);
    }
  }

  Future<Map<String, dynamic>> delete(String path) async {
    try {
      final response = await _dio.delete(path);
      return _handleResponse(response);
    } on DioException catch (e) {
      return _handleError(e);
    }
  }
}
