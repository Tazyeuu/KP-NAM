import '../../../../core/storage/secure_storage.dart';
import '../../domain/model/task_model.dart';
import '../../domain/repository/task_repository.dart';
import '../datasource/task_remote_datasource.dart';

class TaskRepositoryImpl implements TaskRepository {
  final _datasource = TaskRemoteDatasource();

  Future<({String token, int teknisiId})> _getSession() async {
    final token = await SecureStorage.getToken();
    final userIdStr = await SecureStorage.getUserId();

    if (token == null || userIdStr == null) {
      throw Exception('Sesi tidak valid. Silakan login ulang.');
    }

    return (token: token, teknisiId: int.parse(userIdStr));
  }

  @override
  Future<List<TaskModel>> getMyTasks(int teknisiId) async {
    final session = await _getSession();
    return _datasource.getMyTasks(
      teknisiId: session.teknisiId,
      token: session.token,
    );
  }

  @override
  Future<void> startTask({
    required int ticketId,
    required int teknisiId,
  }) async {
    final session = await _getSession();
    return _datasource.startTask(
      ticketId: ticketId,
      teknisiId: session.teknisiId,
      token: session.token,
    );
  }

  @override
  Future<void> resolveTicket({
    required int ticketId,
    required int teknisiId,
    required String note,
  }) async {
    final session = await _getSession();
    return _datasource.resolveTicket(
      ticketId: ticketId,
      teknisiId: session.teknisiId,
      note: note,
      token: session.token,
    );
  }
}
