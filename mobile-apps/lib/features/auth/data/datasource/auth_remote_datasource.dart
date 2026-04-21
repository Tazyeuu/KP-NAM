//lib/features/auth/data/datasource/auth_remote_datasource.dart
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
}
