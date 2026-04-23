import '../../domain/model/user_model.dart';
import '../../domain/repository/auth_repository.dart';
import '../datasource/auth_remote_datasource.dart';
import '../../../../core/storage/secure_storage.dart';
import '../../../../core/services/fcm_service.dart';

class AuthRepositoryImpl implements AuthRepository {
  final _datasource = AuthRemoteDatasource();

  @override
  Future<UserModel> login({
    required String email,
    required String password,
  }) async {
    // 1. Hit API login
    final user = await _datasource.login(email: email, password: password);

    // 2. Simpan sesi ke secure storage
    await SecureStorage.saveSession(
      token: user.token,
      name: user.name,
      email: user.email,
      userId: user.id,
    );

    return user;
  }

  @override
  Future<void> updateFcmToken() async {
    try {
      final token = await SecureStorage.getToken();
      final userIdStr = await SecureStorage.getUserId();
      final fcmToken = await FcmService.getToken();

      // Kalau salah satu tidak ada, skip saja
      if (token == null || userIdStr == null || fcmToken == null) return;

      await _datasource.updateFcmToken(
        teknisiId: int.parse(userIdStr),
        fcmToken: fcmToken,
        token: token,
      );
    } catch (e) {
      // FCM token update gagal tidak perlu crash app
    }
  }
}
