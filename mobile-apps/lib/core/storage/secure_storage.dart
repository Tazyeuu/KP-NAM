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
  static const _keyNotifInApp = 'notif_in_app';
  static const _keyNotifSound = 'notif_sound';
  static const _keyNotifVibrate = 'notif_vibrate';

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

  // Preferensi Notifikasi — FCM selalu aktif, ini hanya preferensi tampilan
  static Future<void> saveNotifInApp(bool enabled) =>
      _storage.write(key: _keyNotifInApp, value: enabled.toString());
  static Future<bool> getNotifInApp() async {
    final value = await _storage.read(key: _keyNotifInApp);
    return value != 'false'; // default true
  }

  static Future<void> saveNotifSound(bool enabled) =>
      _storage.write(key: _keyNotifSound, value: enabled.toString());
  static Future<bool> getNotifSound() async {
    final value = await _storage.read(key: _keyNotifSound);
    return value != 'false'; // default true
  }

  static Future<void> saveNotifVibrate(bool enabled) =>
      _storage.write(key: _keyNotifVibrate, value: enabled.toString());
  static Future<bool> getNotifVibrate() async {
    final value = await _storage.read(key: _keyNotifVibrate);
    return value != 'false'; // default true
  }

  // Timer Start Time — simpan per ticket ID
  static String _timerKey(int ticketId) => 'timer_start_$ticketId';

  static Future<void> saveTimerStart(int ticketId, DateTime startTime) =>
      _storage.write(
        key: _timerKey(ticketId),
        value: startTime.toIso8601String(),
      );

  static Future<DateTime?> getTimerStart(int ticketId) async {
    final value = await _storage.read(key: _timerKey(ticketId));
    if (value == null) return null;
    return DateTime.tryParse(value);
  }

  static Future<void> clearTimerStart(int ticketId) =>
      _storage.delete(key: _timerKey(ticketId));

  // Hapus sesi saja, FCM token dan preferensi tetap tersimpan
  static Future<void> clearSession() async {
    await Future.wait([
      _storage.delete(key: _keyToken),
      _storage.delete(key: _keyUserName),
      _storage.delete(key: _keyUserEmail),
      _storage.delete(key: _keyUserId),
    ]);
  }
}
