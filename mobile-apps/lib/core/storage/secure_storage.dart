import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorage {
  SecureStorage._();

  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  static const _keyToken = 'auth_token';
  static const _keyUserName = 'user_name';
  static const _keyUserEmail = 'user_email';
  static const _keyUserId = 'user_id';
  static const _keyFcmToken = 'fcm_token';

  static Future<void> saveSession({
    required String token,
    required String name,
    required String email,
    required int userId,
  }) async {
    await Future.wait([
      _storage.write(key: _keyToken, value: token),
      _storage.write(key: _keyUserName, value: name),
      _storage.write(key: _keyUserEmail, value: email),
      _storage.write(key: _keyUserId, value: userId.toString()),
    ]);
  }

  static Future<String?> getToken() => _storage.read(key: _keyToken);
  static Future<String?> getUserName() => _storage.read(key: _keyUserName);
  static Future<String?> getUserEmail() => _storage.read(key: _keyUserEmail);
  static Future<String?> getUserId() => _storage.read(key: _keyUserId);

  // FCM Token
  static Future<void> saveFcmToken(String token) =>
      _storage.write(key: _keyFcmToken, value: token);
  static Future<String?> getFcmToken() => _storage.read(key: _keyFcmToken);

  // Hapus sesi saja, FCM token tetap tersimpan
  static Future<void> clearSession() async {
    await Future.wait([
      _storage.delete(key: _keyToken),
      _storage.delete(key: _keyUserName),
      _storage.delete(key: _keyUserEmail),
      _storage.delete(key: _keyUserId),
    ]);
  }
}
