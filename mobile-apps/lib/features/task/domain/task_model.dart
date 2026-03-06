class TaskModel {
  TaskModel({
    required this.id,
    required this.title,
    required this.roomName,
    required this.targetLat,
    required this.targetLng,
    this.status = 'pending',
  });

  final String id;
  final String title;
  final String roomName;
  final double targetLat;
  final double targetLng;
  final String status;
}
