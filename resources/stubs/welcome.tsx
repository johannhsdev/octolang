// octolang:processed
// Referencia de integración OctoLang para React/Inertia.js
// ESTE ARCHIVO NO SE PUBLICA — es solo una guía de ejemplo.
//
// Para integrar OctoLang en tu welcome page:
//   1. Importa LocaleSwitcher en tu archivo existente
//   2. Coloca <LocaleSwitcher /> dentro de tu <nav> o <header>
//
// Ejemplo:
//
//   import LocaleSwitcher from '@/components/octolang/LocaleSwitcher'
//
//   // Dentro del JSX de tu nav:
//   <nav className="flex items-center justify-end gap-4">
//       <LocaleSwitcher />
//       {/* ... resto de tu nav ... */}
//   </nav>

import { Head, Link } from '@inertiajs/react'
import LocaleSwitcher from '@/components/octolang/LocaleSwitcher'

export default function Welcome() {
    return (
        <>
            <Head title="Welcome" />
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
                <header className="mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
                    <nav className="flex items-center justify-end gap-4">
                        <LocaleSwitcher />
                        {/* tus links existentes aquí */}
                    </nav>
                </header>
                {/* tu contenido existente aquí */}
            </div>
        </>
    )
}
