import '../../domain/model/user_model.dart';
import '../../domain/repository/auth_repository.dart';
import '../datasource/auth_remote_datasource.dart';
import '../../../../core/storage/secure_storage.dart';

class AuthRepositoryImpl implements AuthRepository {
  final _datasource = AuthRemoteDatasource();

  @override
  Future<UserModel> login({
    required String email,
    required String password,
  }) async {
    final user = await _datasource.login(email: email, password: password);

    await SecureStorage.saveSession(
      token: user.token,
      name: user.name,
      email: user.email,
      userId: user.id, // ← tambah ini
    );

    return user;
  }
}
