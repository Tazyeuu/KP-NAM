import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorage {
  SecureStorage._();

  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  static const _keyToken = 'auth_token';
  static const _keyUserName = 'user_name';
  static const _keyUserEmail = 'user_email';
  static const _keyUserId = 'user_id'; // ← tambah ini

  static Future<void> saveSession({
    required String token,
    required String name,
    required String email,
    required int userId, // ← tambah ini
  }) async {
    await Future.wait([
      _storage.write(key: _keyToken, value: token),
      _storage.write(key: _keyUserName, value: name),
      _storage.write(key: _keyUserEmail, value: email),
      _storage.write(key: _keyUserId, value: userId.toString()), // ← tambah ini
    ]);
  }

  static Future<String?> getToken() => _storage.read(key: _keyToken);
  static Future<String?> getUserName() => _storage.read(key: _keyUserName);
  static Future<String?> getUserEmail() => _storage.read(key: _keyUserEmail);
  static Future<String?> getUserId() =>
      _storage.read(key: _keyUserId); // ← tambah ini

  static Future<void> clearSession() => _storage.deleteAll();
}
