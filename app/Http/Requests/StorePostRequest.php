<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StorePostRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|min:5|max:200|unique:posts,title',
            'content' => 'required|min:100',
            'category_id' => 'required|exists:categories,id',
            'featured_image' => 'nullable|image|max:2048',
        ];
    }

    // ✅ MENSAJES CLAROS Y ESPECÍFICOS
    public function messages()
    {
        return [
            // ❌ Malo: "The title field is required."
            // ✅ Bueno: Mensaje específico con contexto
            'title.required' => 'El título es obligatorio. Por favor ingresa un título para tu post.',
            
            // ❌ Malo: "The title must be at least 5 characters."
            // ✅ Bueno: Explicar el porqué
            'title.min' => 'El título debe tener al menos 5 caracteres para ser descriptivo.',
            
            // ❌ Malo: "The title may not be greater than 200 characters."
            // ✅ Bueno: Sugerir solución
            'title.max' => 'El título es muy largo (máximo 200 caracteres). Intenta resumirlo o usar el campo de contenido.',
            
            // ❌ Malo: "The title has already been taken."
            // ✅ Bueno: Indicar problema y sugerir acción
            'title.unique' => 'Ya existe un post con ese título. Por favor elige un título diferente o edita el post existente.',
            
            'content.required' => 'El contenido del post no puede estar vacío.',
            'content.min' => 'El contenido debe tener al menos 100 caracteres. Actualmente tiene :attribute caracteres.',
            
            // ❌ Malo: "The selected category id is invalid."
            // ✅ Bueno: Guiar hacia la solución
            'category_id.required' => 'Debes seleccionar una categoría para tu post.',
            'category_id.exists' => 'La categoría seleccionada no es válida. Por favor selecciona una categoría de la lista.',
            
            // ✅ Errores de archivo con información útil
            'featured_image.image' => 'El archivo debe ser una imagen (JPG, PNG o GIF).',
            'featured_image.max' => 'La imagen es muy grande. El tamaño máximo es 2MB. Intenta comprimirla antes de subirla.',
        ];
    }

    // ✅ Personalizar respuesta de error con información adicional
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            $errors = $validator->errors();
            
            throw new ValidationException($validator, response()->json([
                'message' => 'Los datos proporcionados no son válidos',
                'errors' => $errors->messages(),
                // ✅ Información adicional útil
                'help' => [
                    'Si necesitas ayuda, consulta la guía de creación de posts',
                    'Puedes guardar como borrador y terminar después'
                ],
                'actions' => [
                    ['label' => 'Ver guía', 'url' => route('help.posts')],
                    ['label' => 'Guardar borrador', 'url' => route('posts.draft')]
                ]
            ], 422));
        }

        parent::failedValidation($validator);
    }
}