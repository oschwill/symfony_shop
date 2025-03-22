import { NotificationHandler } from "./NotificationHandler";

export class SuccessHandler extends NotificationHandler {
  static showSuccess(form, message, className) {
    this.showNotification(form, message, className, "alert-success");
  }
}
