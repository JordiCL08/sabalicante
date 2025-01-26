<div class="accordion accordion-flush" id="accordionFamilias">
  <?php
  //Obtener las familias activas
  $familias = $gestorSubFamilias->obtener_familias(); 
  
  if ($familias && count($familias) > 0) {
    foreach ($familias as $familia) {
      // Generar ID único para cada familia en el acordeón
      $idAcordeon = "flush-collapse-" . $familia['id_familia']; //Usamos $familia['id_familia'] porque la función devuelve un array
      $idCabecera = "flush-heading-" . $familia['id_familia']; 
      //Obtener las subfamilias de esta familia
      $subfamilias = $gestorSubFamilias->obtener_subfamilias($familia['id_familia']); // array de objetos
  ?>
    <div class="accordion-item">
      <!-- Cabecera de la familia -->
      <h3 class="accordion-header" id="<?= $idCabecera ?>">
        <button
          class="accordion-button collapsed bg-secondary text-white fs-4"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#<?= $idAcordeon ?>"
          aria-expanded="false"
          aria-controls="<?= $idAcordeon ?>">
          <?= htmlspecialchars($familia['nombre']) ?>
        </button>
      </h3>
      <!-- Subfamilias (cuerpo del acordeón) -->
      <div id="<?= $idAcordeon ?>" class="accordion-collapse collapse" aria-labelledby="<?= $idCabecera ?>" data-bs-parent="#accordionFamilias">
        <div class="accordion-body bg-secondary text-dark">
          <div class="list-group list-group-light">
            <!-- Enlace para ver todos los productos de la familia -->
            <a href="index.php?familia=<?= $familia['id_familia'] ?>" class="list-group-item list-group-item-action px-3 border-0 text-white bg-secondary">
              Ver todo
            </a>
            <?php 
            if ($subfamilias && count($subfamilias) > 0) {
              // Si existen subfamilias, las listamos
              foreach ($subfamilias as $subfamilia) { ?>
                <a href="index.php?familia=<?= $familia['id_familia'] ?>&subfamilia=<?= $subfamilia->id_subfamilia ?>" class="list-group-item list-group-item-action px-3 border-0 text-white bg-secondary">
                  <?= htmlspecialchars($subfamilia->nombre) ?>
                </a>
              <?php } 
            } else { ?>
              <p class="text-white">No hay subfamilias disponibles.</p>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  <?php 
    }
  } else {
    echo "<p class='text-white'>No se encontraron familias disponibles.</p>";
  }
  ?>
</div>
