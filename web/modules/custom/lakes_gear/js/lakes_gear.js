document.addEventListener('DOMContentLoaded', function() {
  var lakes = document.querySelectorAll('.lake-list-item');
  for (let i = 0; i < lakes.length; i++) {
    setTimeout(function() {
      lakes[i].style.opacity = 1;
      lakes[i].style.transform = 'translateY(0)';
    }, 250 * i); // 200ms opóźnienia dla każdego kolejnego obrazu
  }
});

document.addEventListener('DOMContentLoaded', function() {
  var searchInput = document.querySelector('.product-search');
  var checkboxesContainer = document.getElementById('edit-associated-products');

  if (searchInput && checkboxesContainer) {
    searchInput.addEventListener('input', function(e) {
      var searchText = e.target.value.toLowerCase();

      checkboxesContainer.querySelectorAll('.form-checkbox').forEach(function(checkbox) {
        var label = checkbox.nextElementSibling.textContent.toLowerCase();
        if (label.includes(searchText)) {
          checkbox.parentElement.style.display = '';
        } else {
          checkbox.parentElement.style.display = 'none';
        }
      });
    });
  } else {
    console.log('Element searchInput or checkboxesContainer not found');
  }
});

document.addEventListener('DOMContentLoaded', function() {
  // Funkcja do filtrowania produktów
  function filterProducts() {
    var searchText = searchInput.value.toLowerCase();

    productList.forEach(function(product) {
      var productName = product.querySelector('.field--name-title a').textContent.toLowerCase();
      if (productName.includes(searchText)) {
        product.style.display = '';
      } else {
        product.style.display = 'none';
      }
    });
  }

  var searchInput = document.querySelector('#product-search-input');
  var productList = document.querySelectorAll('.lake-product-item');

  // Dodanie nasłuchiwania zdarzeń na polu wyszukiwania
  if (searchInput) {
    searchInput.addEventListener('input', filterProducts);
  }
});

document.addEventListener('DOMContentLoaded', function() {
  var searchInput = document.getElementById('lake-search-input');
  var lakesList = document.querySelectorAll('.lake-list-item');

  searchInput.addEventListener('input', function(e) {
    var searchText = e.target.value.toLowerCase();

    lakesList.forEach(function(lake) {
      var lakeName = lake.querySelector('h2').textContent.toLowerCase();
      if (lakeName.includes(searchText)) {
        lake.style.display = '';
      } else {
        lake.style.display = 'none';
      }
    });
  });
});

