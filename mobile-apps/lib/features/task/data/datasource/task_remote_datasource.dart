import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../../domain/model/task_model.dart';

class TaskRemoteDatasource {
  Future<List<TaskModel>> getMyTasks({
    required int teknisiId,
    required String token,
  }) async {
    final response = await ApiClient.get(
      endpoint: '${AppConstants.tasksEndpoint}/$teknisiId',
      token: token,
    );

    final bool isSuccess = response['success'] == true;
    if (!isSuccess) {
      throw Exception(response['message'] ?? 'Gagal mengambil daftar tugas.');
    }

    final List<dynamic> data = response['data'];
    return data.map((json) => TaskModel.fromJson(json)).toList();
  }

  Future<void> startTask({
    required int ticketId,
    required int teknisiId,
    required String token,
  }) async {
    final response = await ApiClient.post(
      endpoint: AppConstants.startTaskEndpoint,
      body: {'ticket_id': ticketId, 'teknisi_id': teknisiId},
      token: token,
    );

    final bool isSuccess = response['success'] == true;
    if (!isSuccess) {
      throw Exception(response['message'] ?? 'Gagal memulai pekerjaan.');
    }
  }

  Future<void> resolveTicket({
    required int ticketId,
    required int teknisiId,
    required String note,
    required String token,
  }) async {
    final response = await ApiClient.post(
      endpoint: AppConstants.resolveTicketEndpoint,
      body: {'ticket_id': ticketId, 'teknisi_id': teknisiId, 'note': note},
      token: token,
    );

    final bool isSuccess = response['success'] == true;
    if (!isSuccess) {
      throw Exception(response['message'] ?? 'Gagal menyelesaikan tiket.');
    }
  }
}
