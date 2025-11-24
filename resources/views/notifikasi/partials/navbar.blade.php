<!-- Navbar -->
<nav class="relative flex flex-wrap items-center justify-between px-0 py-2 mx-6 transition-all shadow-none duration-250 ease-soft rounded-2xl lg:flex-nowrap lg:justify-start"
    navbar-main navbar-scroll="true">
    <div class="flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit">
        <nav>
            <!-- Breadcrumb Dinamis -->
            @include('partials.breadcrumb')
        </nav>

        <div class="flex items-center mt-2 grow sm:mt-0 sm:mr-6 md:mr-0 lg:flex lg:basis-auto">
            <!-- Search -->
            <div class="flex items-center md:ml-auto md:pr-4">
            </div>

            <!-- Right Side Icons -->
            <ul class="flex flex-row justify-end pl-0 mb-0 list-none md-max:w-full">
                <!-- Notifications -->
                <li class="relative flex items-center px-4">
                    <a href="javascript:;" class="block p-0 text-sm transition-all ease-nav-brand text-slate-500"
                        dropdown-trigger aria-expanded="false">
                        <i class="cursor-pointer fa fa-bell"></i>
                        @if ($jumlahNotifikasiBelumDibaca > 0)
                            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                        @endif
                    </a>

                    <ul dropdown-menu
                        class="text-sm transform-dropdown ..."> <!-- class dropdown tetap sama -->
                        <li class="relative mb-2">
                            <div class="flex items-center justify-between px-4 py-2">
                                <h6 class="mb-0 text-sm font-semibold">Notifikasi</h6>
                                <a href="javascript:;" onclick="showNotificationsTab()"
                                    class="text-xs text-blue-600 hover:text-blue-800">Lihat Semua</a>
                            </div>
                        </li>

                        @forelse($notifikasiTerbaru as $notifikasi)
                            <li class="relative mb-2">
                                <a href="{{ route('notifikasi.show', $notifikasi->id) }}"
                                    class="ease-soft py-1.2 clear-both block w-full whitespace-nowrap rounded-lg bg-transparent px-4 duration-300 hover:bg-gray-200 hover:text-slate-700 lg:transition-colors">
                                    <div class="flex py-1">
                                        <div class="my-auto mr-3">
                                            @switch($notifikasi->tipe)
                                                @case('barang_masuk')
                                                    <div class="flex items-center justify-center w-8 h-8 text-center rounded-full bg-green-100 text-green-600">
                                                        <i class="fas fa-arrow-down"></i>
                                                    </div>
                                                    @break
                                                @case('barang_keluar')
                                                    <div class="flex items-center justify-center w-8 h-8 text-center rounded-full bg-red-100 text-red-600">
                                                        <i class="fas fa-arrow-up"></i>
                                                    </div>
                                                    @break
                                                @case('audit')
                                                    <div class="flex items-center justify-center w-8 h-8 text-center rounded-full bg-blue-100 text-blue-600">
                                                        <i class="fas fa-search"></i>
                                                    </div>
                                                    @break
                                                @case('pengadaan')
                                                    <div class="flex items-center justify-center w-8 h-8 text-center rounded-full bg-purple-100 text-purple-600">
                                                        <i class="fas fa-shopping-cart"></i>
                                                    </div>
                                                    @break
                                                @case('stok_kritis')
                                                    <div class="flex items-center justify-center w-8 h-8 text-center rounded-full bg-yellow-100 text-yellow-600">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </div>
                                                    @break
                                                @case('barang_rusak_hilang')
                                                    <div class="flex items-center justify-center w-8 h-8 text-center rounded-full bg-red-100 text-red-600">
                                                        <i class="fas fa-exclamation-circle"></i>
                                                    </div>
                                                    @break
                                                @default
                                                    <div class="flex items-center justify-center w-8 h-8 text-center rounded-full bg-gray-100 text-gray-600">
                                                        <i class="fas fa-info-circle"></i>
                                                    </div>
                                            @endswitch
                                        </div>
                                        <div class="flex flex-col justify-center">
                                            <h6 class="mb-1 text-sm font-normal leading-normal">
                                                {{ $notifikasi->judul }}
                                            </h6>
                                            <p class="mb-0 text-xs leading-tight text-slate-400">
                                                {{ \Carbon\Carbon::parse($notifikasi->created_at)->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="relative mb-2">
                                <a href="javascript:;"
                                    class="ease-soft py-1.2 clear-both block w-full whitespace-nowrap rounded-lg bg-transparent px-4 duration-300 lg:transition-colors">
                                    <div class="flex py-1">
                                        <div class="flex flex-col justify-center">
                                            <h6 class="mb-1 text-sm font-normal leading-normal">
                                                Tidak ada notifikasi baru
                                            </h6>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforelse

                        <li class="relative mb-2">
                            <div class="px-4 py-2">
                                <a href="javascript:;" id="markAllAsRead"
                                    class="text-xs text-blue-600 hover:text-blue-800">Tandai semua sudah dibaca</a>
                            </div>
                        </li>
                    </ul>
                </li>

                <!-- User Dropdown -->
                <li class="flex items-center px-4">
                    <a href="javascript:;" class="block p-0 text-sm transition-all ease-nav-brand text-slate-500" dropdown-trigger>
                        <i class="fas fa-user-circle"></i>
                        <span class="ml-2 hidden sm:inline">{{ auth()->user()->name }}</span>
                    </a>
                    <ul dropdown-menu class="...">
                        <li><a class="..." href="#">Profil</a></li>
                        <li><hr class="..."></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="... w-full text-left">
                                    <i class="fa fa-sign-out-alt mr-2"></i> Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>

                <!-- Mobile Menu Trigger -->
                <li class="flex items-center pl-4 xl:hidden">
                    <a href="javascript:;" class="block p-0 text-sm transition-all ease-nav-brand text-slate-500"
                        sidenav-trigger>
                        <div class="w-4.5 overflow-hidden">
                            <i class="ease-soft mb-0.75 relative block h-0.5 rounded-sm bg-slate-500 transition-all"></i>
                            <i class="ease-soft mb-0.75 relative block h-0.5 rounded-sm bg-slate-500 transition-all"></i>
                            <i class="ease-soft relative block h-0.5 rounded-sm bg-slate-500 transition-all"></i>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
