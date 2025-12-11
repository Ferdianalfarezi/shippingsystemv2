<x-guest-layout>

    <style>
        /* Input styling override */
        .form-control {
            border-radius: 6px !important;
            border: 1px solid #ccc !important;
            box-shadow: none !important;
        }

        .form-control:focus {
            border-color: #ffffff !important;
            box-shadow: 0 0 4px rgba(120, 120, 120, 0.4) !important;
        }
    </style>

    <!-- Bootstrap Login Card -->
    <div class="card shadow-sm p-5"
     style="max-width: 420px; width: 100%; border-radius: 12px; 
            background-color: rgba(255, 255, 255, 0.821);">

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert alert-success text-center mb-3">
                {{ session('status') }}
            </div>
        @endif

        <h2 class="text-center mb-4 fs-4 fw-bolder">LOGIN</h2>


        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Username -->
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input 
                    id="username" 
                    type="text" 
                    class="form-control @error('username') is-invalid @enderror"
                    name="username"
                    value="{{ old('username') }}"
                    required 
                    autofocus
                >
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input 
                    id="password"  
                    type="password"  
                    class="form-control @error('password') is-invalid @enderror"
                    name="password" 
                    required
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="form-check mb-3">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="remember" 
                    name="remember" 
                    checked
                >
                <label class="form-check-label" for="remember">Ingat Saya</label>
            </div>

            <!-- Button -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-dark px-4">
                    Log in
                </button>
            </div>

        </form>
    </div>

</x-guest-layout>
