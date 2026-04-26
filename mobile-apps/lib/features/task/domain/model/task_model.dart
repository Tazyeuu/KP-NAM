class TicketLogModel {
  const TicketLogModel({
    required this.id,
    required this.statusTo,
    required this.note,
    required this.createdAt,
  });

  final int id;
  final String statusTo;
  final String note;
  final DateTime createdAt;

  factory TicketLogModel.fromJson(Map<String, dynamic> json) {
    return TicketLogModel(
      id: json['id'],
      statusTo: json['status_to'] ?? '',
      note: json['note'] ?? '',
      createdAt: DateTime.parse(json['created_at']),
    );
  }
}

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
    required this.updatedAt,
    this.logs = const [], // ← tambah
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
  final DateTime updatedAt;
  final List<TicketLogModel> logs; // ← tambah

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
      updatedAt: DateTime.parse(json['updated_at']),
      // Parse logs kalau ada, kalau tidak return list kosong
      logs: json['logs'] != null
          ? (json['logs'] as List)
                .map((log) => TicketLogModel.fromJson(log))
                .toList()
          : [],
    );
  }
}
