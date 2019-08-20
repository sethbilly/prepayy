<h5 class="m-t-lg with-border">Branding Settings</h5>
<div class="row">
    <div class="col-lg-12">
        <p>
            The code below is for branding. Please modify with the branding information for the institution being set up.
        </p>
        <textarea rows="15" class="form-control"
                  name="style">{{old('style', isset($brandStyle) ? $brandStyle->style : '')}}</textarea>
    </div>
</div>