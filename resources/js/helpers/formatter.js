import dayjs from "dayjs";

export function formatDateForEditing(date, format = 'YYYY-MM-DD') {
  return dayjs(date).format(format);
}
