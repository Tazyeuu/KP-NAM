import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../data/repository/task_repository_impl.dart';
import '../domain/model/task_model.dart';
import '../../../core/storage/secure_storage.dart';

enum TaskStatus { idle, loading, success, error }

class TaskProvider extends ChangeNotifier {
  static const double _maxDistanceMeters = 25;

  final _repository = TaskRepositoryImpl();

  // === State Daftar Tugas ===
  List<TaskModel> _tasks = [];
  TaskStatus _taskStatus = TaskStatus.idle;
  String _errorMessage = '';

  List<TaskModel> get tasks => _tasks;
  TaskStatus get taskStatus => _taskStatus;
  String get errorMessage => _errorMessage;
  bool get isLoadingTasks => _taskStatus == TaskStatus.loading;

  // === State Check-in ===
  bool isLoading = false;
  bool isSuccess = false;
  String checkInError = '';

  // === State Resolve ===
  bool isResolving = false;
  bool isResolved = false;
  String resolveError = '';

  // === Ambil Daftar Tugas ===
  Future<void> fetchMyTasks() async {
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

  // === Proses Check-in + Start Task ===
  Future<void> processCheckIn(TaskModel task) async {
    _setCheckInState(isLoading: true, isSuccess: false, error: '');

    try {
      // 1. Validasi lokasi
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        _setCheckInState(
          isLoading: false,
          error: 'Layanan lokasi tidak aktif.',
        );
        return;
      }

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

      final position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
        ),
      );

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

      // 2. Lokasi valid → hit API start task
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

      _setCheckInState(isLoading: false, isSuccess: true);
    } catch (e) {
      final message = e.toString().replaceFirst('Exception: ', '');
      _setCheckInState(isLoading: false, error: message);
    }
  }

  // === Resolve Ticket ===
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
    this.resolveError = error;
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
    this.checkInError = error;
    notifyListeners();
  }
}
