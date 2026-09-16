<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\Director\StoreCatalogLessonRequest;
use App\Models\CatalogItem;
use App\Models\CatalogLesson;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CatalogoAulaController extends Controller
{
    public function __construct(private CatalogService $catalog) {}

    public function store(StoreCatalogLessonRequest $request, CatalogItem $catalogo): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $catalogo);

        $this->catalog->createLesson($catalogo, $request->validated() + [
            'video_file' => $request->file('video_file'),
            'material_file' => $request->file('material_file'),
        ]);

        return $this->respondAfterSave($request, $catalogo, 'Aula adicionada.');
    }

    public function update(StoreCatalogLessonRequest $request, CatalogItem $catalogo, CatalogLesson $aula): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $catalogo);
        $this->assertBelongsTo($catalogo, $aula);

        $this->catalog->updateLesson($aula, $request->validated() + [
            'video_file' => $request->file('video_file'),
            'material_file' => $request->file('material_file'),
        ]);

        return $this->respondAfterSave($request, $catalogo, 'Aula atualizada.');
    }

    public function destroy(CatalogItem $catalogo, CatalogLesson $aula): RedirectResponse
    {
        $this->authorize('update', $catalogo);
        $this->assertBelongsTo($catalogo, $aula);

        $this->catalog->deleteLesson($aula);

        return redirect()
            ->route('diretor.catalogo.show', $catalogo)
            ->with('success', 'Aula removida.');
    }

    public function reorder(Request $request, CatalogItem $catalogo): RedirectResponse
    {
        $this->authorize('update', $catalogo);

        $data = $request->validate([
            'ordem' => ['required', 'array'],
            'ordem.*' => ['integer'],
        ]);

        $this->catalog->reorderLessons($catalogo, $data['ordem']);

        return back()->with('success', 'Ordem das aulas atualizada.');
    }

    private function assertBelongsTo(CatalogItem $item, CatalogLesson $lesson): void
    {
        abort_unless((int) $lesson->catalog_item_id === (int) $item->id, 404);
    }

    private function respondAfterSave(Request $request, CatalogItem $catalogo, string $message): RedirectResponse|JsonResponse
    {
        $url = route('diretor.catalogo.show', $catalogo);

        if ($request->expectsJson() || $request->ajax()) {
            session()->flash('success', $message);

            return response()->json([
                'redirect' => $url,
                'message' => $message,
            ]);
        }

        return redirect()->to($url)->with('success', $message);
    }
}
