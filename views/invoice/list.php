<table class="table table-bordered">
    <thead>
        <tr>
            <th><?= $lang['invoice_number'] ?></th>
            <th><?= $lang['customer'] ?></th>
            <th><?= $lang['date'] ?></th>
            <th><?= $lang['total'] ?></th>
            <th><?= $lang['status'] ?></th>
            <th><?= $lang['actions'] ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($invoices)): ?>
            <tr>
                <td colspan="6" class="text-center"><?= $lang['no_invoices'] ?></td>
            </tr>
        <?php else: ?>
            <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td><?= $invoice['invoice_number'] ?></td>
                    <td><?= $invoice['customer_name'] ?></td>
                    <td><?= $invoice['invoice_date'] ?></td>
                    <td><?= $invoice['total_amount'] ?></td>
                    <td><?= $invoice['status'] ?></td>
                    <td class="text-end">
                        <a href="invoice_pdf.php?id=<?= $invoice['id'] ?>" class="btn btn-sm btn-success" target="_blank">
                            <i class="bi bi-printer"></i> <?= $lang['print'] ?>
                        </a>
                        <a href="index.php?page=invoice&action=edit&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil"></i> <?= $lang['edit'] ?>
                        </a>
                        <a href="index.php?page=invoice&action=delete&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?= $lang['confirm_delete'] ?>')">
                            <i class="bi bi-trash"></i> <?= $lang['delete'] ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

