export function calculateQuarterActivityProgress(types, targets, activities) {
  let totalWeight = 0;
  for (const type of types) {
    totalWeight += Number(type.weight) || 0;
  }

  let weightedProgress = 0;
  for (const type of types) {
    const weight = Number(type.weight) || 0;
    const detail = targets.find((d) => Number(d.type_id) === Number(type.id));
    const activity = activities?.[type.id];

    const targetQty = detail ? Number(detail.quarter_qty) || 0 : 0;
    const activityQty = activity ? Number(activity.quarter_qty) || 0 : 0;

    if (targetQty > 0) {
      const progressPerType = Math.min(activityQty / targetQty, 1);
      weightedProgress += progressPerType * (weight / totalWeight);
    }
  }

  return weightedProgress * 100;
}

export function calculateMonthlyActivityProgress(types, month, targets, activities) {
  let totalWeight = 0;
  for (const type of types) {
    totalWeight += Number(type.weight) || 0;
  }

  let weightedProgress = 0;
  for (const type of types) { 
    const weight = Number(type.weight) || 0;
    const detail = targets.find((d) => Number(d.type_id) === Number(type.id));

    const activity = activities?.[type.id];

    const targetQty = detail ? Number(detail['month'+ month + '_qty']) || 0 : 0;
    const activityQty = activity ? Number(activity['month'+ month + '_qty']) || 0 : 0;

    if (targetQty > 0) {
      const progressPerType = Math.min(activityQty / targetQty, 1);
      weightedProgress += progressPerType * (weight / totalWeight);
    }
  }

  return weightedProgress * 100;
}