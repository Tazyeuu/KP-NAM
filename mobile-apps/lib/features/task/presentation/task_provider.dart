import 'dart:math';

import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';

import '../domain/task_model.dart';

class TaskProvider extends ChangeNotifier {
  static const double _maxDistanceMeters = 30;
  static const double _earthRadiusMeters = 6371000;

  final List<TaskModel> taskList = [
    TaskModel(
      id: 'task-1',
      title: 'Perbaikan Router',
      roomName: 'IGD',
      targetLat: -0.053421,
      targetLng: 109.345678,
    ),
    TaskModel(
      id: 'task-2',
      title: 'Pemeliharaan PC',
      roomName: 'Poli Gigi',
      targetLat: -0.054123,
      targetLng: 109.346789,
    ),
    TaskModel(
      id: 'task-3',
      title: 'Cek Kabel',
      roomName: 'Apotek',
      targetLat: -0.052987,
      targetLng: 109.344321,
    ),
  ];

  bool isLoading = false;
  bool isSuccess = false;
  String errorMessage = '';

  List<TaskModel> get tasks => taskList;

  Future<void> processCheckIn(TaskModel task) async {
    _updateState(isLoading: true, isSuccess: false, errorMessage: '');

    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        _updateState(
          isLoading: false,
          isSuccess: false,
          errorMessage: 'Error: Layanan lokasi tidak aktif',
        );
        return;
      }

      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.denied) {
        _updateState(
          isLoading: false,
          isSuccess: false,
          errorMessage: 'Error: Izin lokasi ditolak',
        );
        return;
      }

      if (permission == LocationPermission.deniedForever) {
        _updateState(
          isLoading: false,
          isSuccess: false,
          errorMessage: 'Error: Izin lokasi ditolak permanen',
        );
        return;
      }

      final position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
        ),
      );

      final distance = _calculateDistanceMeters(
        position.latitude,
        position.longitude,
        task.targetLat,
        task.targetLng,
      );

      if (distance <= _maxDistanceMeters) {
        _updateState(isLoading: false, isSuccess: true, errorMessage: '');
      } else {
        _updateState(
          isLoading: false,
          isSuccess: false,
          errorMessage: 'Error: Anda berada di luar jangkauan lokasi tugas',
        );
      }
    } catch (_) {
      _updateState(
        isLoading: false,
        isSuccess: false,
        errorMessage: 'Error: Gagal mendapatkan lokasi',
      );
    }
  }

  void clearStatus() {
    if (isSuccess || errorMessage.isNotEmpty) {
      isSuccess = false;
      errorMessage = '';
      notifyListeners();
    }
  }

  void _updateState({bool? isLoading, bool? isSuccess, String? errorMessage}) {
    var changed = false;

    if (isLoading != null && isLoading != this.isLoading) {
      this.isLoading = isLoading;
      changed = true;
    }

    if (isSuccess != null && isSuccess != this.isSuccess) {
      this.isSuccess = isSuccess;
      changed = true;
    }

    if (errorMessage != null && errorMessage != this.errorMessage) {
      this.errorMessage = errorMessage;
      changed = true;
    }

    if (changed) {
      notifyListeners();
    }
  }

  double _calculateDistanceMeters(
    double startLatitude,
    double startLongitude,
    double endLatitude,
    double endLongitude,
  ) {
    final dLat = _degToRad(endLatitude - startLatitude);
    final dLon = _degToRad(endLongitude - startLongitude);
    final lat1 = _degToRad(startLatitude);
    final lat2 = _degToRad(endLatitude);

    final a =
        sin(dLat / 2) * sin(dLat / 2) +
        cos(lat1) * cos(lat2) * sin(dLon / 2) * sin(dLon / 2);
    final c = 2 * atan2(sqrt(a), sqrt(1 - a));
    return _earthRadiusMeters * c;
  }

  double _degToRad(double degrees) {
    return degrees * (pi / 180);
  }
}
