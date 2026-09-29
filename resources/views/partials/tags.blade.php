<script>
    document.querySelectorAll('.tag-campo').forEach(function (campo) {
        const tags = campo.querySelector('.tags');
        const tagInput = campo.querySelector('input[type="text"]');
        const maximo = Number(campo.dataset.max || 10);
        const nombre = campo.dataset.nombre || 'habilidades[]';

        function agregarTag(texto) {
            texto = texto.trim();
            if (texto === '' || tags.children.length >= maximo) return;

            const tag = document.createElement('span');
            tag.className = 'tag';
            tag.textContent = texto + ' ';

            const quitar = document.createElement('button');
            quitar.type = 'button';
            quitar.className = 'tag-quitar';
            quitar.textContent = '×';

            const oculto = document.createElement('input');
            oculto.type = 'hidden';
            oculto.name = nombre;
            oculto.value = texto;

            tag.append(quitar, oculto);
            tags.appendChild(tag);
        }

        tagInput.addEventListener('keydown', function (e) {
            if (e.key === ',' || e.key === 'Enter') {
                e.preventDefault();
                agregarTag(tagInput.value);
                tagInput.value = '';
            }
        });

        tags.addEventListener('click', function (e) {
            if (e.target.classList.contains('tag-quitar')) {
                e.target.parentElement.remove();
            }
        });

        tagInput.form.addEventListener('submit', function () {
            agregarTag(tagInput.value);
        });
    });
</script>
