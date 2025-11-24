<!-- Navbar -->
<nav class="relative flex flex-wrap items-center justify-between px-0 py-2 mx-6 transition-all shadow-none duration-250 ease-soft rounded-2xl lg:flex-nowrap lg:justify-start"
    navbar-main navbar-scroll="true">
    <div class="flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit">
        <nav>
            <!-- breadcrumb -->
            <ol class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16">
                <li class="text-sm leading-normal">
                    <a class="opacity-50 text-slate-700" href="javascript:;">Pages</a>
                </li>
                <li class="text-sm pl-2 capitalize leading-normal text-slate-700 before:float-left before:pr-2 before:text-gray-600 before:content-['/']"
                    aria-current="page">
                    @if (request()->is('admin/dashboard'))
                        Dashboard
                    @elseif(request()->is('admin/users*'))
                        Manajemen Pengguna
                    @elseif(request()->is('admin/inventori*'))
                        Master Barang
                    @elseif(request()->is('admin/jadwal-audit*'))
                        Jadwal Audit
                    @elseif(request()->is('admin/laporan*'))
                        Laporan Sistem
                    @elseif(request()->is('admin/kategori*'))
                        Kategori
                    @else
                        Dashboard
                    @endif
                </li>
            </ol>
            <h6 class="mb-0 font-bold capitalize">
                @if (request()->is('admin/dashboard'))
                    Dashboard
                @elseif(request()->is('admin/users*'))
                    Manajemen Pengguna
                @elseif(request()->is('admin/inventori*'))
                    Master Barang
                @elseif(request()->is('admin/jadwal-audit*'))
                    Jadwal Audit
                @elseif(request()->is('admin/laporan*'))
                    Laporan Sistem
                @elseif(request()->is('admin/kategori*'))
                    Kategori
                @else
                    Dashboard
                @endif
            </h6>
        </nav>

        <div class="flex items-center mt-2 grow sm:mt-0 sm:mr-6 md:mr-0 lg:flex lg:basis-auto">
            <!-- Search -->
            <div class="flex items-center md:ml-auto md:pr-4">
            </div>

            <!-- Right Side Icons -->
            <ul class="flex flex-row justify-end pl-0 mb-0 list-none md-max:w-full">
                <!-- Settings -->
                <li class="flex items-center px-4">
                </li>

                <!-- Notifications -->
                <li class="relative flex items-center px-4">
                    <a href="javascript:;" class="block p-0 text-sm transition-all ease-nav-brand text-slate-500"
                        dropdown-trigger aria-expanded="false">
                        <i class="cursor-pointer fa fa-bell"></i>
                        <span id="notificationBadge"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full hidden">
                            0
                        </span>
                    </a>

                    <!-- Dropdown Menu -->
                    <!-- Tambahkan kelas untuk state awal (tersembunyi) dan animasi -->
                    <ul dropdown-menu
                        class="absolute right-0 z-50 w-80 mt-3 bg-white border border-gray-100 rounded-xl shadow-lg opacity-0 pointer-events-none transform-dropdown transition-all duration-300">
                        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200">
                            <h6 class="text-sm font-semibold text-slate-700">Notifikasi</h6>
                            <button id="markAllAsRead"
                                class="text-xs text-blue-500 hover:text-blue-700 focus:outline-none">Tandai
                                Dibaca</button>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <ul id="notificationList" class="p-2">
                                <!-- Notifikasi akan dimuat di sini oleh JavaScript -->
                            </ul>
                        </div>
                        <div class="border-t border-gray-200 px-4 py-2 text-center">
                            <a href="{{ route(auth()->user()->role . '.notifikasi.index') }}"
                                class="text-xs text-blue-500 hover:underline">Lihat Semua</a>
                        </div>
                    </ul>
                </li>


                <!-- Sign Out -->
                <li class="flex items-center px-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="block px-0 py-2 text-sm font-semibold transition-all ease-nav-brand text-slate-500 hover:text-slate-700">
                            <i class="fa fa-sign-out-alt sm:mr-1"></i>
                            <span class="hidden sm:inline">Sign Out</span>
                        </button>
                    </form>
                </li>

                <!-- Mobile Menu Trigger -->
                <li class="flex items-center pl-4 xl:hidden">
                    <a href="javascript:;" class="block p-0 text-sm transition-all ease-nav-brand text-slate-500"
                        sidenav-trigger>
                        <div class="w-4.5 overflow-hidden">
                            <i
                                class="ease-soft mb-0.75 relative block h-0.5 rounded-sm bg-slate-500 transition-all"></i>
                            <i
                                class="ease-soft mb-0.75 relative block h-0.5 rounded-sm bg-slate-500 transition-all"></i>
                            <i class="ease-soft relative block h-0.5 rounded-sm bg-slate-500 transition-all"></i>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
