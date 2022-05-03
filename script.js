async function getCurrentUser() {
  const response = await fetch("api/current-user.php", {
    // "GET", "POST", "PUT", "DELETE"
    method: "GET",
    credentials: "include",
  });

  return await response.json();
}

// Creates/returns an element (DOM object) from a given HTML string
function newElement(html) {
  const template = document.createElement("template");
  template.innerHTML = html.trim();
  return template.content.firstChild;
}

// Convert the given date object to a string in MySQL's date format
function dateToMysqlFormat(date = new Date()) {
  return `${date.getFullYear()}-${
    date.getMonth() + 1
  }-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;
}
