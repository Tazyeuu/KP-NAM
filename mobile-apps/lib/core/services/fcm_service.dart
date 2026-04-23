import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import '../../core/storage/secure_storage.dart';
import '../../features/auth/presentation/notification_landing_page.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {}

class FcmService {
  FcmService._();

  static final _messaging = FirebaseMessaging.instance;
  static final navigatorKey = GlobalKey<NavigatorState>();

  // Tidak perlu await di main() — jalankan di background
  static void initializeInBackground() {
    _initialize().catchError((e) {});
  }

  static Future<void> _initialize() async {
    final settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    if (settings.authorizationStatus == AuthorizationStatus.denied) return;

    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

    await _refreshAndSaveToken();

    _messaging.onTokenRefresh.listen((newToken) async {
      await SecureStorage.saveFcmToken(newToken);
    });

    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);
    FirebaseMessaging.onMessageOpenedApp.listen((message) {
      _navigateFromNotification(message.data);
    });

    final initialMessage = await _messaging.getInitialMessage();
    if (initialMessage != null) {
      await Future.delayed(const Duration(seconds: 1));
      _navigateFromNotification(initialMessage.data);
    }
  }

  static Future<void> _refreshAndSaveToken() async {
    final token = await _messaging.getToken();
    if (token != null) {
      await SecureStorage.saveFcmToken(token);
    }
  }

  static void _handleForegroundMessage(RemoteMessage message) {
    final notification = message.notification;
    if (notification == null) return;

    final context = navigatorKey.currentContext;
    if (context == null) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              notification.title ?? 'Notifikasi Baru',
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
            if (notification.body != null)
              Text(
                notification.body!,
                style: const TextStyle(fontSize: 13, color: Colors.white70),
              ),
          ],
        ),
        backgroundColor: Colors.green[700],
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 4),
        action: SnackBarAction(
          label: 'Lihat',
          textColor: Colors.white,
          onPressed: () => _navigateFromNotification(message.data),
        ),
      ),
    );
  }

  static void _navigateFromNotification(Map<String, dynamic> data) {
    final type = data['type'];
    if (type != 'new_assignment') return;

    final ticketIdStr = data['ticket_id'];
    if (ticketIdStr == null) return;

    final ticketId = int.tryParse(ticketIdStr.toString());
    if (ticketId == null) return;

    navigatorKey.currentState?.pushAndRemoveUntil(
      MaterialPageRoute(
        builder: (_) => NotificationLandingPage(ticketId: ticketId),
      ),
      (route) => false,
    );
  }

  static Future<String?> getToken() => SecureStorage.getFcmToken();
}
