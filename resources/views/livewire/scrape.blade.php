<div>
        <div class="input-group mb-3">
            <span class="input-group-text">Location</span>
            <input type="text" class="form-control" wire:model.live="location"placeholder="Location" aria-label="Location">
            <button type="submit" wire:click="find" class="btn btn-primary" >Find</button>
        </div>

        @if (!empty($message))
        <button class="btn btn-success"> {{$message}}</button>

        @endif

</div>
