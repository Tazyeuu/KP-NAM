import 'package:flutter_dotenv/flutter_dotenv.dart';

class AppConstants {
  AppConstants._();

  static String get baseUrl =>
      dotenv.env['API_BASE_URL'] ?? 'http://localhost:8000';

  static const String loginEndpoint = '/mobile/login';
  static const String tasksEndpoint = '/mobile/tasks';
  static const String startTaskEndpoint = '/mobile/tickets/start';
  static const String resolveTicketEndpoint = '/mobile/tickets/resolve';
  static const String updateFcmTokenEndpoint =
      '/mobile/update-fcm-token'; // ← baru
}
