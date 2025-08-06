export function can(permissionName, page) {
  const user = page?.props?.auth?.user;
  const permissions = page?.props?.auth?.permissions || [];
  const isAdmin = user?.role === 'admin';

  if (isAdmin) return true;
  return permissions.includes(permissionName);
}
