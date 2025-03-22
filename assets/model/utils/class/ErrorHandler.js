import { NotificationHandler } from "./NotificationHandler";

export class ErrorHandler extends NotificationHandler {
  static showError(form, message, className) {
    this.showNotification(form, message, className, "alert-danger");
  }
}
