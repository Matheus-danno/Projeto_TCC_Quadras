<div class="card border-0 shadow-sm mb-3" style="border-radius: 20px;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <p class="fw-bold text-secondary mb-1"><i class="bi bi-wallet2 text-warning me-1"></i> Seus créditos</p>
            <p class="text-muted small mb-0">Ganhos ao cancelar uma reserva escolhendo "crédito". Use o valor total numa próxima quadra.</p>
        </div>
        <span class="fw-bold fs-3" style="color: #FF8C00;">R$ {{ number_format($saldo, 2, ',', '.') }}</span>
    </div>
</div>
