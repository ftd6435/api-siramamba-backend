<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Team;
use App\Modules\Content\Requests\StoreTeamRequest;
use App\Modules\Content\Requests\UpdateTeamRequest;
use App\Modules\Content\Resources\PublicTeamResource;
use App\Modules\Content\Resources\TeamResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Throwable;

class TeamController extends Controller
{
    use ApiResponses, CloudflareUpload;

    private const AVATAR_PATH = 'teams/avatars';

    private const ADMIN_RELATIONS = ['creator', 'updater'];

    public function index()
    {
        $teams = Team::with(self::ADMIN_RELATIONS)->latest()->get();

        return $this->successResponse(
            TeamResource::collection($teams),
            "Membres de l'équipe récupérés avec succès."
        );
    }

    public function publicIndex()
    {
        $teams = Team::where('is_active', true)->latest()->get();

        return $this->successResponse(
            PublicTeamResource::collection($teams),
            "Membres de l'équipe récupérés avec succès."
        );
    }

    public function store(StoreTeamRequest $request)
    {
        $avatar = $this->uploadImage($request->file('avatar'), self::AVATAR_PATH);
        $userId = $request->user()->getAuthIdentifier();
        $team = new Team($request->safe()->except('avatar'));
        $team->avatar = $avatar;
        $team->created_by = $userId;
        $team->updated_by = $userId;

        try {
            $team->save();
        } catch (Throwable $exception) {
            $this->deleteAvatarSafely($avatar);

            throw $exception;
        }

        return $this->successResponse(
            new TeamResource($team->load(self::ADMIN_RELATIONS)),
            "Membre de l'équipe créé avec succès.",
            201
        );
    }

    public function show(Team $team)
    {
        return $this->successResponse(
            new TeamResource($team->load(self::ADMIN_RELATIONS)),
            "Membre de l'équipe récupéré avec succès."
        );
    }

    public function publicShow(Team $team)
    {
        abort_unless($team->is_active, 404);

        return $this->successResponse(
            new PublicTeamResource($team),
            "Membre de l'équipe récupéré avec succès."
        );
    }

    public function update(UpdateTeamRequest $request, Team $team)
    {
        $data = $request->safe()->except('avatar');
        $oldAvatar = $team->avatar;
        $newAvatar = null;

        if ($request->hasFile('avatar')) {
            $newAvatar = $this->uploadImage($request->file('avatar'), self::AVATAR_PATH);
        }

        $team->fill($data);

        if ($newAvatar) {
            $team->avatar = $newAvatar;
        }

        $team->updated_by = $request->user()->getAuthIdentifier();

        try {
            $team->save();
        } catch (Throwable $exception) {
            $this->deleteAvatarSafely($newAvatar);

            throw $exception;
        }

        if ($newAvatar) {
            $this->deleteAvatarSafely($oldAvatar);
        }

        return $this->successResponse(
            new TeamResource($team->refresh()->load(self::ADMIN_RELATIONS)),
            "Membre de l'équipe mis à jour avec succès."
        );
    }

    public function destroy(Team $team)
    {
        $avatar = $team->avatar;
        $team->delete();
        $this->deleteAvatarSafely($avatar);

        return $this->noContentSuccessResponse("Membre de l'équipe supprimé avec succès.");
    }

    private function deleteAvatarSafely(?string $avatar): void
    {
        if (! $avatar) {
            return;
        }

        try {
            $this->deleteImage($avatar, self::AVATAR_PATH);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
