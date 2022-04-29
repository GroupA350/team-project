async function getCurrentUser() {
  const response = await fetch("api/current-user.php", {
    // "GET", "POST", "PUT", "DELETE"
    method: "GET",
    credentials: "include",
  });

  return await response.json();
}
