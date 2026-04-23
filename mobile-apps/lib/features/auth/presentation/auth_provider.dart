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
    _updateState(AuthStatus.loading);

    try {
      final user = await _repository.login(email: email, password: password);
      _userName = user.name;

      // Kirim FCM token ke server setelah login berhasil
      // Tidak perlu await — biarkan jalan di background
      _repository.updateFcmToken();

      _updateState(AuthStatus.success);
    } catch (e) {
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
