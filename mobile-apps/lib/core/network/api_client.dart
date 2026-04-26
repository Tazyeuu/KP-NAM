import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../constants/app_constants.dart';
import '../services/session_service.dart';

class ApiClient {
  ApiClient._();

  static final _client = http.Client();

  static Future<Map<String, dynamic>> post({
    required String endpoint,
    required Map<String, dynamic> body,
    String? token,
  }) async {
    try {
      final uri = Uri.parse('${AppConstants.baseUrl}$endpoint');
      final headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

      final response = await _client
          .post(uri, headers: headers, body: jsonEncode(body))
          .timeout(const Duration(seconds: 15));

      return _handleResponse(response);
    } on TimeoutException {
      throw Exception(
        'Server tidak merespons. Periksa koneksi Anda dan coba lagi.',
      );
    } on SocketException {
      throw Exception('Tidak ada koneksi internet.');
    } on HttpException {
      throw Exception('Terjadi kesalahan pada server.');
    } on FormatException {
      throw Exception('Respon server tidak valid.');
    } catch (e) {
      if (e is Exception) rethrow;
      throw Exception('Terjadi kesalahan: $e');
    }
  }

  static Future<Map<String, dynamic>> get({
    required String endpoint,
    String? token,
  }) async {
    try {
      final uri = Uri.parse('${AppConstants.baseUrl}$endpoint');
      final headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

      final response = await _client
          .get(uri, headers: headers)
          .timeout(const Duration(seconds: 15));

      return _handleResponse(response);
    } on TimeoutException {
      throw Exception(
        'Server tidak merespons. Periksa koneksi Anda dan coba lagi.',
      );
    } on SocketException {
      throw Exception('Tidak ada koneksi internet.');
    } on HttpException {
      throw Exception('Terjadi kesalahan pada server.');
    } on FormatException {
      throw Exception('Respon server tidak valid.');
    } catch (e) {
      if (e is Exception) rethrow;
      throw Exception('Terjadi kesalahan: $e');
    }
  }

  static Map<String, dynamic> _handleResponse(http.Response response) {
    final decoded = jsonDecode(response.body) as Map<String, dynamic>;

    if (response.statusCode == 401) {
      SessionService.handleSessionExpired();
      throw Exception('Sesi Anda telah berakhir. Silakan login ulang.');
    }

    if (response.statusCode < 200 || response.statusCode >= 300) {
      final message = decoded['message'] ?? 'Terjadi kesalahan pada server.';
      throw Exception(message);
    }

    return decoded;
  }
}
