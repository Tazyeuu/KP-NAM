import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../data/repository/task_repository_impl.dart';
import '../domain/model/task_model.dart';
import '../../../core/storage/secure_storage.dart';
import 'dart:async';

enum TaskStatus { idle, loading, success, error }

class TaskProvider extends ChangeNotifier {
  // Radius maksimal check-in
  static const double _maxDistanceMeters = 25;

  // Settings GPS — balance antara akurasi dan baterai
  static const _locationSettings = LocationSettings(
    accuracy: LocationAccuracy.medium, // ← turun dari high ke medium
    distanceFilter: 0,
    timeLimit: Duration(seconds: 10), // ← timeout 10 detik
  );

  final _repository = TaskRepositoryImpl();

  List<TaskModel> _tasks = [];
  TaskStatus _taskStatus = TaskStatus.idle;
  String _errorMessage = '';

  List<TaskModel> get tasks => _tasks;
  TaskStatus get taskStatus => _taskStatus;
  String get errorMessage => _errorMessage;
  bool get isLoadingTasks => _taskStatus == TaskStatus.loading;

  bool isLoading = false;
  bool isSuccess = false;
  String checkInError = '';

  bool isResolving = false;
  bool isResolved = false;
  String resolveError = '';

  // Cache tasks — hindari fetch berulang
  DateTime? _lastFetchTime;
  static const _cacheDuration = Duration(minutes: 2);

  bool get _isCacheValid {
    if (_lastFetchTime == null) return false;
    return DateTime.now().difference(_lastFetchTime!) < _cacheDuration;
  }

  Future<void> fetchMyTasks({bool forceRefresh = false}) async {
    // Pakai cache kalau masih valid dan tidak force refresh
    if (!forceRefresh && _isCacheValid && _tasks.isNotEmpty) return;

    _updateTaskState(TaskStatus.loading);

    try {
      final userIdStr = await SecureStorage.getUserId();
      if (userIdStr == null) {
        _updateTaskState(
          TaskStatus.error,
          message: 'Sesi tidak valid. Silakan login ulang.',
        );
        return;
      }

      final tasks = await _repository.getMyTasks(int.parse(userIdStr));
      _tasks = tasks;
      _lastFetchTime = DateTime.now();
      _updateTaskState(TaskStatus.success);
    } catch (e) {
      final message = e.toString().replaceFirst('Exception: ', '');
      _updateTaskState(TaskStatus.error, message: message);
    }
  }

  void _updateTaskState(TaskStatus status, {String message = ''}) {
    _taskStatus = status;
    _errorMessage = message;
    notifyListeners();
  }

  Future<void> processCheckIn(TaskModel task) async {
    _setCheckInState(isLoading: true, isSuccess: false, error: '');

    try {
      // 1. Cek service lokasi aktif
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        _setCheckInState(
          isLoading: false,
          error: 'Layanan lokasi tidak aktif.',
        );
        return;
      }

      // 2. Cek permission
      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.denied) {
        _setCheckInState(isLoading: false, error: 'Izin lokasi ditolak.');
        return;
      }
      if (permission == LocationPermission.deniedForever) {
        _setCheckInState(
          isLoading: false,
          error: 'Izin lokasi ditolak permanen.',
        );
        return;
      }

      // 3. Ambil posisi dengan timeout
      final position = await Geolocator.getCurrentPosition(
        locationSettings: _locationSettings,
      );

      // 4. Hitung jarak
      final distance = Geolocator.distanceBetween(
        position.latitude,
        position.longitude,
        task.targetLat,
        task.targetLng,
      );

      if (distance > _maxDistanceMeters) {
        _setCheckInState(
          isLoading: false,
          error:
              'Anda berada ${distance.toStringAsFixed(0)}m dari lokasi. Maksimal ${_maxDistanceMeters.toInt()}m.',
        );
        return;
      }

      // 5. Lokasi valid → hit API start task
      final userIdStr = await SecureStorage.getUserId();
      if (userIdStr == null) {
        _setCheckInState(
          isLoading: false,
          error: 'Sesi tidak valid. Silakan login ulang.',
        );
        return;
      }

      await _repository.startTask(
        ticketId: task.id,
        teknisiId: int.parse(userIdStr),
      );

      // 6. Invalidate cache agar list tugas ter-refresh
      _lastFetchTime = null;

      _setCheckInState(isLoading: false, isSuccess: true);
    } on TimeoutException {
      _setCheckInState(
        isLoading: false,
        error: 'GPS timeout. Pastikan Anda berada di area terbuka.',
      );
    } catch (e) {
      final message = e.toString().replaceFirst('Exception: ', '');
      _setCheckInState(isLoading: false, error: message);
    }
  }

  Future<void> resolveTicket({
    required int ticketId,
    required String note,
  }) async {
    _setResolveState(isResolving: true, isResolved: false, error: '');

    try {
      final userIdStr = await SecureStorage.getUserId();
      if (userIdStr == null) {
        _setResolveState(
          isResolving: false,
          error: 'Sesi tidak valid. Silakan login ulang.',
        );
        return;
      }

      await _repository.resolveTicket(
        ticketId: ticketId,
        teknisiId: int.parse(userIdStr),
        note: note,
      );

      // Invalidate cache
      _lastFetchTime = null;

      _setResolveState(isResolving: false, isResolved: true);
    } catch (e) {
      final message = e.toString().replaceFirst('Exception: ', '');
      _setResolveState(isResolving: false, error: message);
    }
  }

  void _setResolveState({
    required bool isResolving,
    bool isResolved = false,
    String error = '',
  }) {
    this.isResolving = isResolving;
    this.isResolved = isResolved;
    resolveError = error;
    notifyListeners();
  }

  void clearStatus() {
    isSuccess = false;
    checkInError = '';
    isResolved = false;
    resolveError = '';
    notifyListeners();
  }

  void _setCheckInState({
    required bool isLoading,
    bool isSuccess = false,
    String error = '',
  }) {
    this.isLoading = isLoading;
    this.isSuccess = isSuccess;
    checkInError = error;
    notifyListeners();
  }
}
