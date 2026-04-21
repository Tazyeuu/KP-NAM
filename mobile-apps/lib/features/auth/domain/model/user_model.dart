//lib/features/auth/domain/model/user_model.dart
class UserModel {
  const UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.token,
  });

  final int id;
  final String name;
  final String email;
  final String token;

  factory UserModel.fromJson(Map<String, dynamic> json) {
    final user = json['data']['user'];
    final token = json['data']['token'];

    return UserModel(
      id: user['id'],
      name: user['name'],
      email: user['email'],
      token: token,
    );
  }
}
