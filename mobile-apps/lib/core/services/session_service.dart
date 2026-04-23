import 'package:flutter/material.dart';
import '../storage/secure_storage.dart';
import '../../features/auth/presentation/login_page.dart';
import 'fcm_service.dart';

class SessionService {
  SessionService._();

  static bool _isHandling = false;

  static void handleSessionExpired() {
    // Hindari multiple redirect sekaligus
    if (_isHandling) return;
    _isHandling = true;

    // Hapus semua data sesi
    SecureStorage.clearSession();

    // Ambil context dari navigatorKey
    final context = FcmService.navigatorKey.currentContext;
    if (context == null) {
      _isHandling = false;
      return;
    }

    // Redirect ke LoginPage
    FcmService.navigatorKey.currentState?.pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginPage()),
      (route) => false,
    );

    // Tampilkan pesan
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final newContext = FcmService.navigatorKey.currentContext;
      if (newContext == null) return;

      ScaffoldMessenger.of(newContext).showSnackBar(
        SnackBar(
          content: const Row(
            children: [
              Icon(Icons.lock_outline, color: Colors.white, size: 18),
              SizedBox(width: 8),
              Text(
                'Sesi Anda telah berakhir. Silakan login ulang.',
                style: TextStyle(color: Colors.white),
              ),
            ],
          ),
          backgroundColor: Colors.orange[700],
          behavior: SnackBarBehavior.floating,
          duration: const Duration(seconds: 4),
        ),
      );

      _isHandling = false;
    });
  }
}
