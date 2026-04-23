import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../../domain/model/user_model.dart';

class AuthRemoteDatasource {
  Future<UserModel> login({
    required String email,
    required String password,
  }) async {
    final response = await ApiClient.post(
      endpoint: AppConstants.loginEndpoint,
      body: {'email': email, 'password': password},
    );

    final bool isSuccess = response['success'] == true;
    if (!isSuccess) {
      throw Exception(response['message'] ?? 'Terjadi kesalahan.');
    }

    return UserModel.fromJson(response);
  }

  Future<void> updateFcmToken({
    required int teknisiId,
    required String fcmToken,
    required String token,
  }) async {
    final response = await ApiClient.post(
      endpoint: AppConstants.updateFcmTokenEndpoint,
      body: {'teknisi_id': teknisiId, 'fcm_token': fcmToken},
      token: token,
    );

    final bool isSuccess = response['success'] == true;
    if (!isSuccess) {
      throw Exception(response['message'] ?? 'Gagal update FCM token.');
    }
  }
}
