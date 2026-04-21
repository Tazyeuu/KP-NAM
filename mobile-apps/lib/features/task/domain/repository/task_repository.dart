import '../model/task_model.dart';

abstract class TaskRepository {
  Future<List<TaskModel>> getMyTasks(int teknisiId);
  Future<void> startTask({required int ticketId, required int teknisiId});
  Future<void> resolveTicket({
    required int ticketId,
    required int teknisiId,
    required String note,
  });
}
