class TaskModel {
  const TaskModel({
    required this.id,
    required this.ticketNumber,
    required this.subject,
    required this.description,
    required this.priority,
    required this.status,
    required this.locationDetail,
    required this.categoryName,
    required this.departmentName,
    required this.targetLat,
    required this.targetLng,
  });

  final int id;
  final String ticketNumber;
  final String subject;
  final String description;
  final String priority;
  final String status;
  final String locationDetail;
  final String categoryName;
  final String departmentName;
  final double targetLat;
  final double targetLng;

  factory TaskModel.fromJson(Map<String, dynamic> json) {
    final department = json['department'];
    final category = json['category'];

    return TaskModel(
      id: json['id'],
      ticketNumber: json['ticket_number'],
      subject: json['subject'],
      description: json['description'],
      priority: json['priority'],
      status: json['status'],
      locationDetail: json['location_detail'],
      categoryName: category['name'],
      departmentName: department['name'],
      targetLat: double.parse(department['latitude'].toString()),
      targetLng: double.parse(department['longitude'].toString()),
    );
  }
}
