<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Result Merger — Merge Academic Results Faster</title>
    <meta name="description" content="Upload test and exam scores, validate, merge, and export final student records from one clean admin system." />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
            colors: {
              navy: { 950: '#070b1a', 900: '#0b1224', 800: '#111a33', 700: '#1a2547' },
              gold: { 400: '#f5c97b', 500: '#e0b15a' },
            },
            boxShadow: {
              glow: '0 0 60px -10px rgba(16,185,129,0.35)',
            },
            animation: {
              'float': 'float 6s ease-in-out infinite',
              'fade-up': 'fadeUp 0.8s ease-out both',
            },
            keyframes: {
              float: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-10px)' } },
              fadeUp: { '0%': { opacity: 0, transform: 'translateY(20px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
            }
          }
        }
      }
    </script>
    <style>
      html { scroll-behavior: smooth; }
      body { font-family: 'Inter', sans-serif; background: #070b1a; color: #e2e8f0; }
      .bg-grid {
        background-image:
          radial-gradient(ellipse 80% 50% at 50% -10%, rgba(16,185,129,0.18), transparent),
          radial-gradient(ellipse 60% 40% at 80% 20%, rgba(59,130,246,0.18), transparent),
          radial-gradient(ellipse 50% 30% at 20% 30%, rgba(245,201,123,0.10), transparent);
      }
      .glass {
        background: rgba(17, 26, 51, 0.55);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(148,163,184,0.12);
      }
      .text-gradient {
        background: linear-gradient(120deg, #ffffff 0%, #a7f3d0 40%, #93c5fd 75%, #f5c97b 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
      }
      .btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 10px 30px -10px rgba(16,185,129,0.55);
      }
      .btn-primary:hover { box-shadow: 0 15px 40px -10px rgba(16,185,129,0.7); transform: translateY(-1px); }
      .ring-glow { box-shadow: 0 0 0 1px rgba(16,185,129,0.25), 0 20px 60px -15px rgba(16,185,129,0.35); }
      .step-line { background: linear-gradient(90deg, rgba(16,185,129,0.6), rgba(59,130,246,0.6)); }
    </style>
</head>
<body class="antialiased">

  {{-- ============ NAVBAR ============ --}}
  <header class="sticky top-0 z-50 glass">
    <nav class="max-w-7xl mx-auto px-6 lg:px-10 h-16 flex items-center justify-between">
      <a href="/" class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-blue-500 flex items-center justify-center shadow-glow">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-navy-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h6l2 3h8M4 7v12a1 1 0 001 1h14a1 1 0 001-1V10"/></svg>
        </div>
        <span class="font-bold text-lg tracking-tight text-white">Result<span class="text-emerald-400">Merger</span></span>
      </a>
      <div class="hidden md:flex items-center gap-8 text-sm text-slate-300">
        <a href="#features" class="hover:text-white transition">Features</a>
        <a href="#workflow" class="hover:text-white transition">Workflow</a>
        <a href="#preview" class="hover:text-white transition">Preview</a>
      </div>
      <a href="/admin" class="btn-primary text-white text-sm font-semibold px-4 py-2 rounded-lg transition">Open Admin</a>
    </nav>
  </header>

  {{-- ============ HERO ============ --}}
  <section class="bg-grid relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 pt-20 pb-28 grid lg:grid-cols-2 gap-14 items-center">
      <div class="animate-fade-up">
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium glass text-emerald-300 border-emerald-500/20">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          Built for academic result processing
        </span>
        <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.05] text-gradient">
          Merge Academic Results Faster and More Accurately
        </h1>
        <p class="mt-6 text-lg text-slate-300 max-w-xl leading-relaxed">
          Upload test scores, upload exam scores, detect issues, merge results, and export final student records from one clean admin system.
        </p>
        <div class="mt-9 flex flex-wrap gap-4">
          <a href="/admin" class="btn-primary inline-flex items-center gap-2 text-white font-semibold px-6 py-3.5 rounded-xl transition">
            Open Admin Panel
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </a>
          <a href="#features" class="inline-flex items-center gap-2 glass text-white font-semibold px-6 py-3.5 rounded-xl hover:bg-white/5 transition">
            View Features
          </a>
        </div>
        <div class="mt-10 flex items-center gap-6 text-xs text-slate-400">
          <div class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> CSV ready</div>
          <div class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Excel export</div>
          <div class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Issue detection</div>
        </div>
      </div>

      {{-- Preview card --}}
      <div class="relative animate-float">
        <div class="absolute -inset-6 bg-gradient-to-tr from-emerald-500/20 via-blue-500/20 to-gold-400/10 blur-3xl rounded-3xl"></div>
        <div class="relative glass rounded-2xl p-6 ring-glow">
          <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full bg-red-400/70"></span>
              <span class="w-3 h-3 rounded-full bg-yellow-400/70"></span>
              <span class="w-3 h-3 rounded-full bg-emerald-400/70"></span>
            </div>
            <span class="text-xs text-slate-400">result-merger.app</span>
          </div>
          <div class="space-y-3">
            @php
              $steps = [
                ['Test Upload', 'CSV validated · 248 rows', 'emerald'],
                ['Exam Upload', 'CSV validated · 248 rows', 'blue'],
                ['Merge', 'Matched by matric_no · 246 records', 'gold'],
                ['Export', 'final_results.xlsx ready', 'emerald'],
              ];
            @endphp
            @foreach($steps as $i => $s)
              <div class="flex items-center gap-4 p-3.5 rounded-xl bg-navy-900/60 border border-white/5 hover:border-emerald-400/30 transition">
                <div class="w-9 h-9 rounded-lg bg-{{ $s[2] === 'gold' ? 'yellow' : $s[2] }}-500/15 text-{{ $s[2] === 'gold' ? 'yellow' : $s[2] }}-300 flex items-center justify-center font-bold text-sm">{{ $i + 1 }}</div>
                <div class="flex-1">
                  <div class="text-sm font-semibold text-white">{{ $s[0] }}</div>
                  <div class="text-xs text-slate-400">{{ $s[1] }}</div>
                </div>
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ STATS ============ --}}
  <section class="max-w-7xl mx-auto px-6 lg:px-10 -mt-10">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      @php
        $stats = [
          ['CSV Uploads', 'Test & exam files', 'M4 16l4-4 4 4 8-8'],
          ['Issue Detection', 'Smart validation', 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'],
          ['Batch Tracking', 'Versioned merges', 'M19 11H5m14-7H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z'],
          ['Excel Export', 'CSV / XLSX ready', 'M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3'],
        ];
      @endphp
      @foreach($stats as $s)
        <div class="glass rounded-2xl p-5 hover:border-emerald-400/30 transition">
          <div class="w-10 h-10 rounded-lg bg-emerald-500/15 text-emerald-300 flex items-center justify-center mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $s[2] }}"/></svg>
          </div>
          <div class="text-white font-semibold">{{ $s[0] }}</div>
          <div class="text-xs text-slate-400 mt-1">{{ $s[1] }}</div>
        </div>
      @endforeach
    </div>
  </section>

  {{-- ============ FEATURES ============ --}}
  <section id="features" class="max-w-7xl mx-auto px-6 lg:px-10 py-24">
    <div class="text-center max-w-2xl mx-auto mb-14">
      <span class="text-xs font-semibold tracking-widest text-emerald-400 uppercase">Features</span>
      <h2 class="mt-3 text-3xl sm:text-4xl font-bold text-white tracking-tight">Everything you need to process results</h2>
      <p class="mt-4 text-slate-400">Purpose-built tools for academic admins — from upload to final export.</p>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
      @php
        $features = [
          ['Test Score Upload', 'Upload student test scores from CSV with validation.', 'emerald', 'M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
          ['Exam Score Upload', 'Upload exam scores and validate against grading settings.', 'blue', 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
          ['Smart Merge Engine', 'Merge records by student ID or matric number.', 'gold', 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
          ['Issue Tracking', 'Detect invalid scores, missing IDs, duplicates, and unmatched records.', 'red', 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'],
          ['Dynamic Grading', 'Manage test max, exam max, total max, and grade guide.', 'blue', 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z'],
          ['Export Center', 'Export final merged results as CSV or Excel.', 'emerald', 'M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3'],
        ];
        $colorMap = ['emerald' => 'emerald', 'blue' => 'blue', 'gold' => 'yellow', 'red' => 'red'];
      @endphp
      @foreach($features as $f)
        @php $c = $colorMap[$f[2]]; @endphp
        <div class="group glass rounded-2xl p-6 hover:border-{{ $c }}-400/40 hover:-translate-y-1 transition duration-300">
          <div class="w-12 h-12 rounded-xl bg-{{ $c }}-500/15 text-{{ $c }}-300 flex items-center justify-center mb-5 group-hover:scale-110 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $f[3] }}"/></svg>
          </div>
          <h3 class="text-lg font-semibold text-white">{{ $f[0] }}</h3>
          <p class="mt-2 text-sm text-slate-400 leading-relaxed">{{ $f[1] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  {{-- ============ WORKFLOW ============ --}}
  <section id="workflow" class="bg-navy-900/40 border-y border-white/5 py-24">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
      <div class="text-center max-w-2xl mx-auto mb-16">
        <span class="text-xs font-semibold tracking-widest text-emerald-400 uppercase">Workflow</span>
        <h2 class="mt-3 text-3xl sm:text-4xl font-bold text-white tracking-tight">Five clean steps to final results</h2>
      </div>
      <div class="grid md:grid-cols-5 gap-5 relative">
        @php
          $flow = ['Upload Test Scores', 'Upload Exam Scores', 'Validate Records', 'Merge Results', 'Export Final Output'];
        @endphp
        @foreach($flow as $i => $t)
          <div class="relative glass rounded-2xl p-6 text-center hover:border-emerald-400/30 transition">
            <div class="w-12 h-12 mx-auto rounded-full step-line text-white font-bold flex items-center justify-center text-lg shadow-glow">{{ $i + 1 }}</div>
            <h3 class="mt-4 font-semibold text-white">{{ $t }}</h3>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ FINAL OUTPUT PREVIEW ============ --}}
  <section id="preview" class="max-w-7xl mx-auto px-6 lg:px-10 py-24">
    <div class="text-center max-w-2xl mx-auto mb-12">
      <span class="text-xs font-semibold tracking-widest text-emerald-400 uppercase">Final Output</span>
      <h2 class="mt-3 text-3xl sm:text-4xl font-bold text-white tracking-tight">A clean, exportable result sheet</h2>
      <p class="mt-4 text-slate-400">Preview of merged records ready for download.</p>
    </div>
    <div class="glass rounded-2xl overflow-hidden ring-glow">
      <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
        <div class="flex items-center gap-2 text-sm text-slate-300">
          <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6h13M9 5h13M3 5h.01M3 11h.01M3 17h.01"/></svg>
          final_results.xlsx
        </div>
        <span class="text-xs px-2.5 py-1 rounded-md bg-emerald-500/15 text-emerald-300 font-medium">Ready to export</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-navy-900/60 text-slate-300">
            <tr>
              @foreach(['student_id', 'matric_no', 'first_name', 'last_name', 'Level', 'college', 'department', 'test_score', 'exam_score', 'total_score'] as $h)
                <th class="text-left font-semibold px-4 py-3 whitespace-nowrap">{{ $h }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            @php
              $rows = [
                ['1028113', '23/026480/HSC', 'John', 'Okafor', '100', 'Science', 'Physics', '35', '55', '90'],
                ['1028114', '23/026481/HSC', 'Mary', 'Ade', '100', 'Science', 'Physics', '28', '49', '77'],
                ['1028115', '23/026482/HSC', 'Daniel', 'Musa', '100', 'Science', 'Physics', '22', '44', '66'],
              ];
            @endphp
            @foreach($rows as $r)
              <tr class="hover:bg-white/5 transition">
                @foreach($r as $i => $cell)
                  <td class="px-4 py-3.5 whitespace-nowrap {{ $i === 9 ? 'font-semibold text-emerald-300' : 'text-slate-300' }}">{{ $cell }}</td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section class="max-w-7xl mx-auto px-6 lg:px-10 pb-24">
    <div class="relative overflow-hidden rounded-3xl glass p-10 sm:p-14 text-center ring-glow">
      <div class="absolute -top-24 -right-24 w-72 h-72 bg-emerald-500/20 blur-3xl rounded-full"></div>
      <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-blue-500/20 blur-3xl rounded-full"></div>
      <div class="relative">
        <h2 class="text-3xl sm:text-5xl font-extrabold text-gradient tracking-tight">Ready to process results with confidence?</h2>
        <p class="mt-5 text-slate-300 max-w-2xl mx-auto">Open the admin panel to upload scores, track issues, merge batches, and export final results.</p>
        <a href="/admin" class="mt-8 inline-flex items-center gap-2 btn-primary text-white font-semibold px-7 py-4 rounded-xl transition">
          Go to Admin Panel
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
      </div>
    </div>
  </section>

  {{-- ============ FOOTER ============ --}}
  <footer class="border-t border-white/5">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-400">
      <div class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-400 to-blue-500"></div>
        <span class="font-semibold text-white">Result<span class="text-emerald-400">Merger</span></span>
      </div>
      <div>&copy; {{ date('Y') }} Result Merger. Built for academic excellence.</div>
    </div>
  </footer>

</body>
</html>
