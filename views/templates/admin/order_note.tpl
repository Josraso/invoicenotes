{*
* Invoice Notes Template
* Path: modules/invoicenotes/views/templates/admin/order_note.tpl
*}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-file-text"></i>
        {l s='Invoice Notes' mod='invoicenotes'}
    </div>
    <div class="panel-body">
        <form action="" method="post" class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Note Title:' mod='invoicenotes'}
                </label>
                <div class="col-lg-9">
                    <input type="text" 
                           name="invoice_note_title" 
                           class="form-control" 
                           value="{$invoice_note_title|escape:'html':'UTF-8'}"
                           placeholder="{l s='Special Notes:' mod='invoicenotes'}">
                    <p class="help-block">
                        {l s='Custom title that will appear above the note on the invoice.' mod='invoicenotes'}
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Note Content:' mod='invoicenotes'}
                </label>
                <div class="col-lg-9">
                    <textarea name="invoice_note" 
                              class="form-control" 
                              rows="4" 
                              placeholder="{l s='Enter special notes for this order invoice...' mod='invoicenotes'}">{$invoice_note|escape:'html':'UTF-8'}</textarea>
                    <p class="help-block">
                        {l s='This note will appear on the invoice PDF for this specific order.' mod='invoicenotes'}
                    </p>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-9 col-lg-offset-3">
                    <button type="submit" name="submitInvoiceNote" class="btn btn-primary">
                        <i class="icon-save"></i>
                        {l s='Save Note' mod='invoicenotes'}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.panel .panel-heading i {
    margin-right: 5px;
}
</style>