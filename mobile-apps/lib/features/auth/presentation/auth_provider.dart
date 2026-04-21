//lib/features/auth/presentation/auth_provider.dart
import 'package:flutter/material.dart';
import '../data/repository/auth_repository_impl.dart';

enum AuthStatus { idle, loading, success, error }

class AuthProvider extends ChangeNotifier {
  final _repository = AuthRepositoryImpl();

  AuthStatus _status = AuthStatus.idle;
  String _errorMessage = '';
  String _userName = '';

  AuthStatus get status => _status;
  String get errorMessage => _errorMessage;
  String get userName => _userName;

  bool get isLoading => _status == AuthStatus.loading;

  Future<void> login({required String email, required String password}) async {
    print('>>> AuthProvider.login() dipanggil'); // ← tambah ini
    _updateState(AuthStatus.loading);

    try {
      print('>>> Memanggil repository...'); // ← tambah ini
      final user = await _repository.login(email: email, password: password);
      _userName = user.name;
      _updateState(AuthStatus.success);
    } catch (e) {
      print('>>> Error: $e'); // ← tambah ini
      final message = e.toString().replaceFirst('Exception: ', '');
      _updateState(AuthStatus.error, message: message);
    }
  }

  void resetStatus() {
    if (_status == AuthStatus.error || _status == AuthStatus.success) {
      _updateState(AuthStatus.idle);
    }
  }

  void _updateState(AuthStatus status, {String message = ''}) {
    _status = status;
    _errorMessage = message;
    notifyListeners();
  }
}
