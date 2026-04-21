//lib/features/auth/domain/repository/auth_repository.dart
import '../model/user_model.dart';

// Abstract class = kontrak/interface
// Data layer WAJIB mengikuti kontrak ini
abstract class AuthRepository {
  Future<UserModel> login({required String email, required String password});
}
