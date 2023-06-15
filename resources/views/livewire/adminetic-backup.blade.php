<div>
    {{-- {{ dd($disks, $backupStatuses, $activeDisk, $files) }} --}}
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-3 box-col-6 pe-0">
                <div class="md-sidebar"><a class="btn btn-primary md-sidebar-toggle" href="javascript:void(0)">file
                        Filter</a>
                    <div class="md-sidebar-aside job-left-aside custom-scrollbar">
                        <div class="file-sidebar">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card mobile-app-card upgrade-plan widget-hover">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div style="width: 80%">
                                                    <h5 class="mb-1">Backup Database</h5>
                                                    <p class="f-light mb-2">Active Drive <span
                                                            class="badge badge-primary">{{ $activeDisk }}</span></p>
                                                    <div wire:loading wire:target="createBackup">
                                                        <button disabled
                                                            class="purchase-btn btn btn-primary btn-hover-effect f-w-500"
                                                            type="button">Processing ..</button>
                                                    </div>
                                                    <div wire:loading.remove wire:target="createBackup">
                                                        <button wire:click="createBackup"
                                                            class="purchase-btn btn btn-primary btn-hover-effect f-w-500"
                                                            type="button">Backup</button>
                                                    </div>
                                                </div>
                                                <div style="width: 20%">
                                                    <div wire:loading wire:target="createBackup">
                                                        <i class="fas fa-spinner fa-spin text-primary"
                                                            style="font-size: 30px;margin: 40px 5px;"></i>
                                                    </div>
                                                    <div wire:loading.remove wire:target="createBackup">
                                                        <i class="fa fa-upload text-primary"
                                                            style="font-size: 30px;margin: 40px 5px;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <ul>
                                        <li>
                                            <div class="btn btn-outline-primary"><i data-feather="grid"> </i>Drives
                                            </div>
                                        </li>
                                        @foreach ($backupStatuses as $backupStatus)
                                            <li>
                                                <div class="pricing-plan">
                                                    <h6>
                                                        <div class="d-flex justify-content-between">
                                                            {{ strtoupper($backupStatus['disk']) }}
                                                            <div>
                                                                <span
                                                                    class="badge badge-{{ $activeDisk == $backupStatus['disk'] ? 'success' : 'danger' }}">{{ $activeDisk == $backupStatus['disk'] ? 'Active' : 'Inactive' }}</span>
                                                                <span
                                                                    class="badge badge-primary">{{ $backupStatus['amount'] }}</span>
                                                            </div>
                                                        </div>
                                                    </h6>
                                                    <h5>{{ $backupStatus['name'] }}</h5>
                                                    <p> <span
                                                            class="badge badge-{{ $backupStatus['healthy'] ? 'success' : 'danger' }}">{{ $backupStatus['healthy'] ? 'Healthy' : 'Unhealthy' }}</span>
                                                    </p>
                                                    <small class="text-muted">Updated
                                                        {{ $backupStatus['newest'] }}</small>
                                                    <p>{{ $backupStatus['usedStorage'] }}</p>
                                                    <img class="bg-img"
                                                        src="{{ asset('adminetic/assets/images/dashboard/folder.png') }}"
                                                        alt="disk">
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-9 col-md-12 box-col-12">
                <div class="file-content">
                    <div class="card">
                        <div class="card-header">
                            <div class="input-group">
                                <span class="input-group-text">Active Disk</span>
                                <select wire:model="activeDisk" class="form-control">
                                    @foreach ($disks as $disk)
                                        <option value="{{ $disk }}">{{ $disk }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="card-body file-manager">
                            <h4 class="mb-3">All Files</h4>
                            @if (count($files ?? []) > 0)
                                <ul class="files">
                                    @foreach ($files as $index => $file)
                                        <li class="file-box">
                                            <div class="file-top">
                                                <div wire:loading.remove wire:target="deleteFile({{ $index }})">
                                                    <i class="fa fa-trash f-14 text-danger"
                                                        wire:click="deleteFile({{ $index }})"
                                                        style="position: absolute;top: 30px;left: 30px;cursor: pointer"></i>
                                                </div>
                                                <div wire:loading wire:target="deleteFile({{ $index }})">
                                                    <i class="fas fa-spinner fa-spin f-14 ellips"
                                                        style="cursor: pointer"></i>
                                                </div>
                                                <i class="fa fa-database txt-primary"></i>
                                                {{-- Download --}}
                                                <div wire:loading.remove
                                                    wire:target="downloadFile('{{ $file['path'] }}')">
                                                    <i class="fa fa-download f-14 ellips" style="cursor: pointer"
                                                        wire:click="downloadFile('{{ $file['path'] }}')"></i>
                                                </div>
                                                <div wire:loading wire:target="downloadFile('{{ $file['path'] }}')">
                                                    <i class="fas fa-spinner fa-spin f-14 ellips"
                                                        style="cursor: pointer"></i>
                                                </div>
                                            </div>
                                            <div class="file-bottom">
                                                <h6>{{ $file['name'] }}
                                                </h6>
                                                <p class="mb-1">{{ $file['size'] }}</p>
                                                <p> <b>Date :
                                                    </b>{{ $file['date']->toDateString() }}({{ $file['date']->diffForHumans() }})
                                                </p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('livewire_third_party')
        <script>
            $(function() {
                Livewire.on('backup_success', message => {
                    var notify_allow_dismiss = Boolean(
                        {{ config('adminetic.notify_allow_dismiss', true) }});
                    var notify_delay = {{ config('adminetic.notify_delay', 2000) }};
                    var notify_showProgressbar = Boolean(
                        {{ config('adminetic.notify_showProgressbar', true) }});
                    var notify_timer = {{ config('adminetic.notify_timer', 300) }};
                    var notify_newest_on_top = Boolean(
                        {{ config('adminetic.notify_newest_on_top', true) }});
                    var notify_mouse_over = Boolean(
                        {{ config('adminetic.notify_mouse_over', true) }});
                    var notify_spacing = {{ config('adminetic.notify_spacing', 1) }};
                    var notify_notify_animate_in =
                        "{{ config('adminetic.notify_animate_in', 'animated fadeInDown') }}";
                    var notify_notify_animate_out =
                        "{{ config('adminetic.notify_animate_out', 'animated fadeOutUp') }}";
                    var notify = $.notify({
                        title: "<i class='{{ config('adminetic.notify_icon', 'fa fa-bell-o') }}'></i> " +
                            "Alert",
                        message: message
                    }, {
                        type: 'success',
                        allow_dismiss: notify_allow_dismiss,
                        delay: notify_delay,
                        showProgressbar: notify_showProgressbar,
                        timer: notify_timer,
                        newest_on_top: notify_newest_on_top,
                        mouse_over: notify_mouse_over,
                        spacing: notify_spacing,
                        animate: {
                            enter: notify_notify_animate_in,
                            exit: notify_notify_animate_out
                        }
                    });
                });
                Livewire.on('backup_error', message => {
                    var notify_allow_dismiss = Boolean(
                        {{ config('adminetic.notify_allow_dismiss', true) }});
                    var notify_delay = {{ config('adminetic.notify_delay', 2000) }};
                    var notify_showProgressbar = Boolean(
                        {{ config('adminetic.notify_showProgressbar', true) }});
                    var notify_timer = {{ config('adminetic.notify_timer', 300) }};
                    var notify_newest_on_top = Boolean(
                        {{ config('adminetic.notify_newest_on_top', true) }});
                    var notify_mouse_over = Boolean(
                        {{ config('adminetic.notify_mouse_over', true) }});
                    var notify_spacing = {{ config('adminetic.notify_spacing', 1) }};
                    var notify_notify_animate_in =
                        "{{ config('adminetic.notify_animate_in', 'animated fadeInDown') }}";
                    var notify_notify_animate_out =
                        "{{ config('adminetic.notify_animate_out', 'animated fadeOutUp') }}";
                    var notify = $.notify({
                        title: "<i class='{{ config('adminetic.notify_icon', 'fa fa-bell-o') }}'></i> " +
                            "Alert",
                        message: message
                    }, {
                        type: 'danger',
                        allow_dismiss: notify_allow_dismiss,
                        delay: notify_delay,
                        showProgressbar: notify_showProgressbar,
                        timer: notify_timer,
                        newest_on_top: notify_newest_on_top,
                        mouse_over: notify_mouse_over,
                        spacing: notify_spacing,
                        animate: {
                            enter: notify_notify_animate_in,
                            exit: notify_notify_animate_out
                        }
                    });
                });
            });
        </script>
    @endpush
</div>
