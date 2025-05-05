<footer class="footer mt-auto py-3 bg-light">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">
                &copy; {{ date('Y') }} {{ config('app.name', 'Portal do Cliente') }}. Todos os direitos reservados.
            </span>
            <span class="text-muted">
                <i class="fas fa-clock me-1"></i> Última atualização: {{ date('d/m/Y H:i') }}
            </span>
        </div>
    </div>
</footer>